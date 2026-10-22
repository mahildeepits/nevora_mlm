<?php

namespace App\Services;

use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use App\Models\Setting;

class PaymentRouteService
{
    /**
     * Resolve the assigned payee, destination wallet, and category for a slot activation
     */
    public function resolvePayee(SlotPosition $position, Slot $slot): array
    {
        $parentPos = SlotPosition::find($position->parent_position_id);

        if (!$parentPos) {
            // Root node fallback: pays Company Root
            $company = User::where('member_id', 'COMPANY01')->firstOrFail();
            return [
                'payee_user' => $company,
                'target_position' => $position,
                'wallet_address' => $company->wallet_address ?: env('COMPANY_REVENUE_WALLET'),
                'amount' => $slot->entry_fee,
                'category' => 'Slot Income',
                'payment_number' => 1,
                'is_company_wallet' => true,
            ];
        }

        // Apply 3-Matrix 13-Payment Rule based on leg position (1, 2, or 3)
        $targetPayeePos = null;

        if ($position->leg_position == 3) {
            // Leg 3 pays direct parent
            $targetPayeePos = $parentPos;
        } elseif ($position->leg_position == 1) {
            // Leg 1 pays Upline 1 (parent's parent)
            $upline1Pos = $parentPos->parent_position_id ? SlotPosition::find($parentPos->parent_position_id) : null;
            $targetPayeePos = $upline1Pos ?: $parentPos;
        } else {
            // Leg 2 pays Upline 2 (grandparent's parent)
            $upline1Pos = $parentPos->parent_position_id ? SlotPosition::find($parentPos->parent_position_id) : null;
            $upline2Pos = ($upline1Pos && $upline1Pos->parent_position_id) ? SlotPosition::find($upline1Pos->parent_position_id) : null;
            $targetPayeePos = $upline2Pos ?: ($upline1Pos ?: $parentPos);
        }

        $payeeUser = User::findOrFail($targetPayeePos->user_id);
        $nextPaymentNumber = $targetPayeePos->received_payments_count + 1;

        // Determine destination wallet address and category
        $companyReserveWallet = getSetting('company_reserve_wallet', env('COMPANY_RESERVE_WALLET', '0x1111111111111111111111111111111111111111'));
        $companyRevenueWallet = getSetting('company_revenue_wallet', env('COMPANY_REVENUE_WALLET', '0x2222222222222222222222222222222222222222'));

        $destinationWallet = $payeeUser->wallet_address;
        $category = 'Slot Income';
        $isCompanyWallet = false;

        if ($slot->slot_number < 12) {
            if (in_array($nextPaymentNumber, [3, 4, 5])) {
                // Payments #3, #4, #5: Reserved for next slot
                $destinationWallet = $companyReserveWallet;
                $category = 'Reserved for Next Slot';
                $isCompanyWallet = true;
            } elseif ($nextPaymentNumber == 13) {
                // Payment #13: Recycle Pool
                $category = 'Recycle Income';
            }
        }

        // Fallback if payee wallet is missing
        if (!$destinationWallet) {
            $destinationWallet = $companyRevenueWallet;
            $isCompanyWallet = true;
        }

        return [
            'payee_user' => $payeeUser,
            'target_position' => $targetPayeePos,
            'wallet_address' => $destinationWallet,
            'amount' => $slot->entry_fee,
            'category' => $category,
            'payment_number' => $nextPaymentNumber,
            'is_company_wallet' => $isCompanyWallet,
        ];
    }

    /**
     * Resolve payee for a Recycle Pool activation
     */
    public function resolveRecyclePayee(RecyclePosition $position, Slot $slot): array
    {
        $parentPos = RecyclePosition::find($position->parent_position_id);

        if (!$parentPos) {
            $company = User::where('member_id', 'COMPANY01')->firstOrFail();
            return [
                'payee_user' => $company,
                'target_position' => $position,
                'wallet_address' => $company->wallet_address ?: env('COMPANY_REVENUE_WALLET'),
                'amount' => $slot->entry_fee,
                'category' => 'Recycle Income',
                'payment_number' => 1,
                'is_company_wallet' => true,
            ];
        }

        $targetPayeePos = null;
        if ($position->leg_position == 3) {
            $targetPayeePos = $parentPos;
        } elseif ($position->leg_position == 1) {
            $upline1Pos = $parentPos->parent_position_id ? RecyclePosition::find($parentPos->parent_position_id) : null;
            $targetPayeePos = $upline1Pos ?: $parentPos;
        } else {
            $upline1Pos = $parentPos->parent_position_id ? RecyclePosition::find($parentPos->parent_position_id) : null;
            $upline2Pos = ($upline1Pos && $upline1Pos->parent_position_id) ? RecyclePosition::find($upline1Pos->parent_position_id) : null;
            $targetPayeePos = $upline2Pos ?: ($upline1Pos ?: $parentPos);
        }

        $payeeUser = User::findOrFail($targetPayeePos->user_id);
        $nextPaymentNumber = $targetPayeePos->received_payments_count + 1;

        $companyRevenueWallet = getSetting('company_revenue_wallet', env('COMPANY_REVENUE_WALLET', '0x2222222222222222222222222222222222222222'));
        $destinationWallet = $payeeUser->wallet_address;
        $category = 'Recycle Income';
        $isCompanyWallet = false;

        if (in_array($nextPaymentNumber, [5, 12])) {
            // Payments #5 and #12 in Recycle Pools go to Company Revenue
            $destinationWallet = $companyRevenueWallet;
            $category = 'Company Deduction';
            $isCompanyWallet = true;
        }

        if (!$destinationWallet) {
            $destinationWallet = $companyRevenueWallet;
            $isCompanyWallet = true;
        }

        return [
            'payee_user' => $payeeUser,
            'target_position' => $targetPayeePos,
            'wallet_address' => $destinationWallet,
            'amount' => $slot->entry_fee,
            'category' => $category,
            'payment_number' => $nextPaymentNumber,
            'is_company_wallet' => $isCompanyWallet,
        ];
    }
}
