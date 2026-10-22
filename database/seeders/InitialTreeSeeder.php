<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use App\Models\Setting;
use App\Models\Announcement;
use Illuminate\Support\Facades\Hash;

class InitialTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@nevora.com'],
            [
                'member_id' => 'ADMIN01',
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'mobile' => '+91 9999999999',
                'role_id' => 1,
                'is_paid' => true,
                'current_slot_id' => 12,
                'wallet_address' => '0x0000000000000000000000000000000000000001',
            ]
        );

        // 2. Company Root User (Account A)
        $company = User::updateOrCreate(
            ['member_id' => 'COMPANY01'],
            [
                'name' => 'Company Root (A)',
                'email' => 'company@nevora.com',
                'password' => Hash::make('password'),
                'mobile' => '+91 8888888888',
                'role_id' => 2,
                'is_paid' => true,
                'current_slot_id' => 12,
                'sponsor_id' => null,
                'parent_id' => null,
                'parent_string' => null,
                'wallet_address' => '0x1111111111111111111111111111111111111111',
            ]
        );
        $company->parent_string = (string) $company->id;
        $company->save();

        // 3. Default Direct Child (Account B)
        $memberB = User::updateOrCreate(
            ['member_id' => 'MEMBER01'],
            [
                'name' => 'Root Member (B)',
                'email' => 'member@nevora.com',
                'password' => Hash::make('password'),
                'mobile' => '+91 7777777777',
                'role_id' => 2,
                'is_paid' => true,
                'current_slot_id' => 12,
                'sponsor_id' => $company->member_id,
                'parent_id' => $company->member_id,
                'parent_string' => $company->id . ',' . $company->id, // updated below
                'wallet_address' => '0x2222222222222222222222222222222222222222',
            ]
        );
        $memberB->parent_string = $company->id . ',' . $memberB->id;
        $memberB->save();

        // 4. Pre-place both A and B across all 12 slots
        $allSlots = Slot::orderBy('slot_number', 'asc')->get();

        foreach ($allSlots as $slot) {
            // Place Company (A) at Root of this Slot
            $companyPos = SlotPosition::updateOrCreate(
                [
                    'user_id' => $company->id,
                    'slot_id' => $slot->id,
                ],
                [
                    'slot_number' => $slot->slot_number,
                    'parent_position_id' => null,
                    'child_position_ids' => [],
                    'leg_position' => 1,
                    'level' => 1,
                    'received_payments_count' => 0,
                    'is_completed' => false,
                ]
            );

            // Place Member B under Company (A) in this Slot
            $memberBPos = SlotPosition::updateOrCreate(
                [
                    'user_id' => $memberB->id,
                    'slot_id' => $slot->id,
                ],
                [
                    'slot_number' => $slot->slot_number,
                    'parent_position_id' => $companyPos->id,
                    'child_position_ids' => [],
                    'leg_position' => 1,
                    'level' => 2,
                    'received_payments_count' => 0,
                    'is_completed' => false,
                ]
            );

            // Update Company A's child array
            $companyPos->child_position_ids = [$memberBPos->id];
            $companyPos->save();

            // 5. Pre-place in Recycle Pool Cycle 1 if slot supports recycle
            if ($slot->max_recycles > 0) {
                $companyRecycle = RecyclePosition::updateOrCreate(
                    [
                        'user_id' => $company->id,
                        'slot_id' => $slot->id,
                        'cycle_number' => 1,
                    ],
                    [
                        'slot_number' => $slot->slot_number,
                        'parent_position_id' => null,
                        'child_position_ids' => [],
                        'leg_position' => 1,
                        'level' => 1,
                        'received_payments_count' => 0,
                        'is_completed' => false,
                    ]
                );

                $memberBRecycle = RecyclePosition::updateOrCreate(
                    [
                        'user_id' => $memberB->id,
                        'slot_id' => $slot->id,
                        'cycle_number' => 1,
                    ],
                    [
                        'slot_number' => $slot->slot_number,
                        'parent_position_id' => $companyRecycle->id,
                        'child_position_ids' => [],
                        'leg_position' => 1,
                        'level' => 2,
                        'received_payments_count' => 0,
                        'is_completed' => false,
                    ]
                );

                $companyRecycle->child_position_ids = [$memberBRecycle->id];
                $companyRecycle->save();
            }
        }

        // 6. Default Settings
        $settings = [
            'software_name' => 'NEVORA',
            'company_reserve_wallet' => '0x1111111111111111111111111111111111111111',
            'company_revenue_wallet' => '0x2222222222222222222222222222222222222222',
            'usdt_contract' => '0x55d398326f99059fF775485246999027B3197955',
            'support_email' => 'support@nevora.com',
            'network_name' => 'BNB Smart Chain (BEP-20)',
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // 7. Initial Announcement
        Announcement::updateOrCreate(
            ['title' => 'Welcome to NEVORA 3-Matrix Hybrid Platform!'],
            [
                'content' => 'Welcome to NEVORA! Our next-generation 3-Matrix 12-Slot hybrid platform with direct P2P on-chain tracking is officially live. Connect your BEP-20 wallet and activate Slot 1 ($15 USDT) to start earning direct matrix and global autopool incomes.',
                'type' => 'info',
                'is_active' => true,
            ]
        );
    }
}
