<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\SlotUpgrade;
use App\Models\UnifiedTransaction;
use App\Models\User;
use App\Services\P2PBlockchainService;
use App\Services\TreePlacementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotUpgradeController extends Controller
{
    protected P2PBlockchainService $blockchainService;
    protected TreePlacementService $treePlacementService;

    public function __construct(P2PBlockchainService $blockchainService, TreePlacementService $treePlacementService)
    {
        $this->blockchainService = $blockchainService;
        $this->treePlacementService = $treePlacementService;
    }

    /**
     * Display the list of users eligible for 3-Payment Reserved Slot Upgrades.
     */
    public function index(Request $request)
    {
        // 1. Self-healing detection: Find all positions where received_payments_count >= 5 in slots 1-11
        // and ensure a SlotUpgrade record exists if user hasn't yet upgraded
        $this->syncEligiblePositions();

        $status = $request->get('status', 'pending');

        $query = SlotUpgrade::with(['user', 'slotPosition', 'admin', 'fromSlot', 'toSlot'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('slot')) {
            $query->where('from_slot_number', (int) $request->slot);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', function ($uq) use ($s) {
                    $uq->where('member_id', 'LIKE', "%$s%")
                       ->orWhere('name', 'LIKE', "%$s%")
                       ->orWhere('wallet_address', 'LIKE', "%$s%");
                })->orWhere('tx_hash', 'LIKE', "%$s%");
            });
        }

        $upgrades = $query->paginate(20)->withQueryString();

        // Platform KPIs
        $pendingCount = SlotUpgrade::where('status', 'pending')->count();
        $pendingAmount = (float) SlotUpgrade::where('status', 'pending')->sum('reserved_amount');
        $completedCount = SlotUpgrade::where('status', 'completed')->count();
        $totalPaidAmount = (float) SlotUpgrade::where('status', 'completed')->sum('reserved_amount');

        $slots = Slot::orderBy('slot_number')->get();
        $companyReserveWallet = getSetting('company_reserve_wallet', env('COMPANY_RESERVE_WALLET', '0x06e2d3C60171d39f2d407C6714B34E8f3e7cE62A'));
        $usdtContract = env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955');

        return view('admin.upgrades.index', compact(
            'upgrades',
            'status',
            'pendingCount',
            'pendingAmount',
            'completedCount',
            'totalPaidAmount',
            'slots',
            'companyReserveWallet',
            'usdtContract'
        ));
    }

    /**
     * Process Admin "Click & Pay" transfer and auto-upgrade user into the next slot.
     */
    public function payAndUpgrade(Request $request, SlotUpgrade $upgrade)
    {
        $request->validate([
            'tx_hash' => 'required|string|max:100',
            'admin_wallet' => 'nullable|string|max:60',
        ]);

        if ($upgrade->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'This upgrade request has already been processed.',
            ], 422);
        }

        $user = $upgrade->user;
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User associated with this upgrade request was not found.',
            ], 404);
        }

        if (empty($user->wallet_address)) {
            return response()->json([
                'status' => false,
                'message' => "Member {$user->member_id} does not have a linked BEP-20 wallet address.",
            ], 422);
        }

        $txHash = trim($request->input('tx_hash'));
        $adminWallet = strtolower(trim($request->input('admin_wallet') ?: (getSetting('company_reserve_wallet') ?: env('COMPANY_RESERVE_WALLET', ''))));
        $userWallet = strtolower(trim($user->wallet_address));
        $expectedAmount = (float) $upgrade->reserved_amount;

        // Check if tx_hash already used in unified transactions
        $existingTx = UnifiedTransaction::where('tx_hash', $txHash)->first();
        if ($existingTx) {
            return response()->json([
                'status' => false,
                'message' => 'This transaction hash has already been registered in the system.',
            ], 422);
        }

        // On-chain or local verification
        $isSimulated = (app()->environment('local') && str_starts_with($txHash, '0xtest_'))
                    || str_starts_with($txHash, '0xadmin_manual_');

        if (!$isSimulated) {
            $verification = $this->blockchainService->verifyTransfer($adminWallet, $userWallet, $expectedAmount, $txHash);
            if (!$verification['status']) {
                // If RPC verification is still pending or delayed, also check with flexible payer address
                return response()->json([
                    'status' => false,
                    'message' => $verification['message'] ?? 'Transaction verification failed on BNB Smart Chain.',
                ], 400);
            }
        }

        $nextSlot = Slot::where('slot_number', $upgrade->to_slot_number)->firstOrFail();
        $currentSlot = Slot::where('slot_number', $upgrade->from_slot_number)->firstOrFail();

        return DB::transaction(function () use ($upgrade, $user, $currentSlot, $nextSlot, $txHash, $adminWallet, $expectedAmount) {
            $adminUser = Auth::user();

            // 1. Mark SlotUpgrade as completed
            $upgrade->status = 'completed';
            $upgrade->tx_hash = $txHash;
            $upgrade->admin_id = $adminUser ? $adminUser->id : 1;
            $upgrade->admin_wallet = $adminWallet;
            $upgrade->user_wallet = $user->wallet_address;
            $upgrade->paid_at = now();
            $upgrade->save();

            // 2. Record Debit transaction in UnifiedTransaction (reduces dashboard reserved wallet)
            UnifiedTransaction::create([
                'user_id' => $user->id,
                'from_user_id' => $adminUser ? $adminUser->id : 1,
                'slot_id' => $currentSlot->id,
                'slot_number' => $currentSlot->slot_number,
                'amount' => $expectedAmount,
                'transaction_type' => 'Debit',
                'category' => 'Reserved for Next Slot',
                'status' => 'Completed',
                'tx_hash' => $txHash,
                'from_address' => $adminWallet,
                'to_address' => $user->wallet_address,
                'description' => "Slot {$nextSlot->slot_number} ({$nextSlot->name}) Upgrade Payout of $" . number_format($expectedAmount, 2) . " USDT transferred by Admin",
            ]);

            // 3. Transfer & place user into next slot Autopool
            $this->treePlacementService->placeInAutopool($user, $nextSlot);

            // 4. Update user's current slot
            if ($user->current_slot_id < $nextSlot->slot_number) {
                $user->current_slot_id = $nextSlot->slot_number;
                $user->save();
            }

            Log::info("Admin paid \${$expectedAmount} USDT to {$user->member_id} ({$user->wallet_address}) and upgraded to Slot {$nextSlot->slot_number}. Tx: {$txHash}");

            return response()->json([
                'status' => true,
                'message' => "Successfully transferred \${$expectedAmount} USDT to Member {$user->member_id} and upgraded to Slot {$nextSlot->slot_number} ({$nextSlot->name})!",
                'tx_hash' => $txHash,
                'next_slot' => $nextSlot->slot_number,
            ]);
        });
    }

    /**
     * Self-healing sync to populate pending upgrades for any user who completed 3 reserved payments.
     */
    protected function syncEligiblePositions(): void
    {
        $eligiblePositions = SlotPosition::where('received_payments_count', '>=', 5)
            ->where('slot_number', '<', 12)
            ->get();

        foreach ($eligiblePositions as $pos) {
            $user = User::find($pos->user_id);
            if (!$user) continue;

            $nextSlotNumber = $pos->slot_number + 1;
            $slot = Slot::where('slot_number', $pos->slot_number)->first();
            if (!$slot) continue;

            // Check if user is already in next slot
            $alreadyInNextSlot = SlotPosition::where('user_id', $user->id)
                ->where('slot_number', $nextSlotNumber)
                ->exists();

            if ($alreadyInNextSlot) {
                // If user is already placed, check if completed record exists
                SlotUpgrade::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'from_slot_number' => $pos->slot_number,
                    ],
                    [
                        'slot_position_id' => $pos->id,
                        'to_slot_number' => $nextSlotNumber,
                        'reserved_amount' => (float) ($slot->entry_fee * 3),
                        'reserved_payments_count' => 3,
                        'user_wallet' => $user->wallet_address,
                        'status' => 'completed',
                        'paid_at' => now(),
                    ]
                );
            } else {
                // Create pending upgrade record if not already created
                SlotUpgrade::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'from_slot_number' => $pos->slot_number,
                    ],
                    [
                        'slot_position_id' => $pos->id,
                        'to_slot_number' => $nextSlotNumber,
                        'reserved_amount' => (float) ($slot->entry_fee * 3),
                        'reserved_payments_count' => 3,
                        'user_wallet' => $user->wallet_address,
                        'status' => 'pending',
                    ]
                );
            }
        }
    }
}

