<?php

namespace App\Services;

use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use App\Models\UnifiedTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatrixIncomeService
{
    protected TreePlacementService $treePlacementService;
    protected PaymentRouteService $paymentRouteService;

    public function __construct(TreePlacementService $treePlacementService, PaymentRouteService $paymentRouteService)
    {
        $this->treePlacementService = $treePlacementService;
        $this->paymentRouteService = $paymentRouteService;
    }

    /**
     * Process confirmed slot activation payment
     */
    public function processSlotActivation(User $payer, Slot $slot, array $verificationResult): array
    {
        return DB::transaction(function () use ($payer, $slot, $verificationResult) {
            $slotPos = SlotPosition::where('user_id', $payer->id)
                ->where('slot_id', $slot->id)
                ->firstOrFail();

            // 1. Resolve payee
            $routing = $this->paymentRouteService->resolvePayee($slotPos, $slot);
            $payeeUser = $routing['payee_user'];
            $targetPos = $routing['target_position'];
            $category = $routing['category'];
            $amount = $routing['amount'];
            $txHash = $verificationResult['tx_hash'];

            // 2. Mark payer active
            $payer->is_paid = true;
            if ($payer->current_slot_id < $slot->slot_number) {
                $payer->current_slot_id = $slot->slot_number;
            }
            $payer->save();

            // 3. Increment payee's received payment count
            $targetPos->received_payments_count += 1;
            $currentPaymentCount = $targetPos->received_payments_count;

            if ($currentPaymentCount >= 13) {
                $targetPos->is_completed = true;
            }
            $targetPos->save();

            // 4. Log in Unified Transactions
            UnifiedTransaction::create([
                'user_id' => $payeeUser->id,
                'from_user_id' => $payer->id,
                'slot_id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'amount' => $amount,
                'transaction_type' => 'Credit',
                'category' => $category,
                'status' => 'Completed',
                'tx_hash' => $txHash,
                'from_address' => $payer->wallet_address,
                'to_address' => $routing['wallet_address'],
                'description' => "Payment #{$currentPaymentCount} in Slot {$slot->slot_number} ({$slot->name}) from Member {$payer->member_id}",
            ]);

            // 5. Check Auto-Upgrade to Next Slot upon Payment #5
            if ($currentPaymentCount === 5 && $slot->slot_number < 12) {
                $nextSlot = Slot::where('slot_number', $slot->slot_number + 1)->first();
                if ($nextSlot) {
                    $this->treePlacementService->placeInAutopool($payeeUser, $nextSlot);

                    Log::info("User {$payeeUser->member_id} auto-upgraded to Slot {$nextSlot->slot_number}");
                }
            }

            // 6. Check Recycle Pool Entry upon Payment #13
            if ($currentPaymentCount >= 13 && $slot->max_recycles > 0) {
                // Find next cycle number
                $existingCycles = RecyclePosition::where('user_id', $payeeUser->id)
                    ->where('slot_id', $slot->id)
                    ->count();

                if ($existingCycles < $slot->max_recycles) {
                    $nextCycle = $existingCycles + 1;
                    $this->treePlacementService->placeInRecyclePool($payeeUser, $slot, $nextCycle);

                    Log::info("User {$payeeUser->member_id} entered Recycle Pool for Slot {$slot->slot_number} (Cycle {$nextCycle})");
                }
            }

            return [
                'status' => true,
                'message' => "Slot {$slot->slot_number} activated successfully! Transaction verified on BSC.",
                'payee' => $payeeUser->member_id,
                'payment_number' => $currentPaymentCount,
            ];
        });
    }

    /**
     * Process confirmed Recycle Pool payment
     */
    public function processRecyclePayment(User $payer, Slot $slot, RecyclePosition $recyclePos, array $verificationResult): array
    {
        return DB::transaction(function () use ($payer, $slot, $recyclePos, $verificationResult) {
            $routing = $this->paymentRouteService->resolveRecyclePayee($recyclePos, $slot);
            $payeeUser = $routing['payee_user'];
            $targetPos = $routing['target_position'];
            $category = $routing['category'];
            $amount = $routing['amount'];
            $txHash = $verificationResult['tx_hash'];

            $targetPos->received_payments_count += 1;
            $currentPaymentCount = $targetPos->received_payments_count;

            if ($currentPaymentCount >= 13) {
                $targetPos->is_completed = true;
            }
            $targetPos->save();

            UnifiedTransaction::create([
                'user_id' => $payeeUser->id,
                'from_user_id' => $payer->id,
                'slot_id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'amount' => $amount,
                'transaction_type' => 'Credit',
                'category' => $category,
                'status' => 'Completed',
                'tx_hash' => $txHash,
                'from_address' => $payer->wallet_address,
                'to_address' => $routing['wallet_address'],
                'description' => "Recycle Payment #{$currentPaymentCount} (Cycle {$targetPos->cycle_number}) in Slot {$slot->slot_number} from Member {$payer->member_id}",
            ]);

            // Re-cycle entry upon Payment #13
            if ($currentPaymentCount >= 13) {
                $existingCycles = RecyclePosition::where('user_id', $payeeUser->id)
                    ->where('slot_id', $slot->id)
                    ->count();

                if ($existingCycles < $slot->max_recycles) {
                    $nextCycle = $existingCycles + 1;
                    $this->treePlacementService->placeInRecyclePool($payeeUser, $slot, $nextCycle);
                }
            }

            return [
                'status' => true,
                'message' => 'Recycle payment verified and credited.',
            ];
        });
    }
}
