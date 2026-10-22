<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slot;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slots = [
            [
                'slot_number' => 1,
                'name' => 'Slot 1 - Starter',
                'entry_fee' => 15.00,
                'reserved_unit_amount' => 15.00,
                'reserved_count' => 3,
                'company_deduction' => 0.00,
                'net_payout_amount' => 45.00,
                'max_recycles' => 11,
                'matrix_type' => 'working_tree',
                'is_active' => true,
            ],
            [
                'slot_number' => 2,
                'name' => 'Slot 2 - Bronze',
                'entry_fee' => 45.00,
                'reserved_unit_amount' => 15.00,
                'reserved_count' => 3,
                'company_deduction' => 0.00,
                'net_payout_amount' => 45.00,
                'max_recycles' => 10,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 3,
                'name' => 'Slot 3 - Silver',
                'entry_fee' => 135.00,
                'reserved_unit_amount' => 42.00,
                'reserved_count' => 3,
                'company_deduction' => 9.00,
                'net_payout_amount' => 126.00,
                'max_recycles' => 9,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 4,
                'name' => 'Slot 4 - Gold',
                'entry_fee' => 378.00,
                'reserved_unit_amount' => 119.00,
                'reserved_count' => 3,
                'company_deduction' => 21.00,
                'net_payout_amount' => 357.00,
                'max_recycles' => 8,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 5,
                'name' => 'Slot 5 - Platinum',
                'entry_fee' => 1071.00,
                'reserved_unit_amount' => 344.00,
                'reserved_count' => 3,
                'company_deduction' => 39.00,
                'net_payout_amount' => 1032.00,
                'max_recycles' => 7,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 6,
                'name' => 'Slot 6 - Diamond',
                'entry_fee' => 3096.00,
                'reserved_unit_amount' => 1005.00,
                'reserved_count' => 3,
                'company_deduction' => 81.00,
                'net_payout_amount' => 3015.00,
                'max_recycles' => 6,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 7,
                'name' => 'Slot 7 - Crown',
                'entry_fee' => 9045.00,
                'reserved_unit_amount' => 2961.00,
                'reserved_count' => 3,
                'company_deduction' => 162.00,
                'net_payout_amount' => 8883.00,
                'max_recycles' => 5,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 8,
                'name' => 'Slot 8 - Royal',
                'entry_fee' => 26649.00,
                'reserved_unit_amount' => 8776.00,
                'reserved_count' => 3,
                'company_deduction' => 321.00,
                'net_payout_amount' => 26328.00,
                'max_recycles' => 4,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 9,
                'name' => 'Slot 9 - Ambassador',
                'entry_fee' => 78984.00,
                'reserved_unit_amount' => 26115.00,
                'reserved_count' => 3,
                'company_deduction' => 639.00,
                'net_payout_amount' => 78345.00,
                'max_recycles' => 3,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 10,
                'name' => 'Slot 10 - Legend',
                'entry_fee' => 236313.00,
                'reserved_unit_amount' => 77918.00,
                'reserved_count' => 3,
                'company_deduction' => 1281.00,
                'net_payout_amount' => 233754.00,
                'max_recycles' => 2,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 11,
                'name' => 'Slot 11 - Elite',
                'entry_fee' => 703824.00,
                'reserved_unit_amount' => 232901.00,
                'reserved_count' => 3,
                'company_deduction' => 2559.00,
                'net_payout_amount' => 698703.00,
                'max_recycles' => 1,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
            [
                'slot_number' => 12,
                'name' => 'Slot 12 - Infinity',
                'entry_fee' => 2096109.00,
                'reserved_unit_amount' => 696996.00,
                'reserved_count' => 3,
                'company_deduction' => 5121.00,
                'net_payout_amount' => 2090988.00,
                'max_recycles' => 0,
                'matrix_type' => 'autopool',
                'is_active' => true,
            ],
        ];

        foreach ($slots as $slotData) {
            Slot::updateOrCreate(
                ['slot_number' => $slotData['slot_number']],
                $slotData
            );
        }
    }
}
