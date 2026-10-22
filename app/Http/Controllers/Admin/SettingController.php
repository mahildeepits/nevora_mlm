<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\Slot;
use App\Models\SlotPosition;
use App\Models\RecyclePosition;
use App\Models\UnifiedTransaction;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display the System Settings and Maintenance page.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $stats = [
            'total_users' => User::count(),
            'regular_members' => User::whereNotIn('member_id', ['ADMIN01', 'COMPANY01', 'MEMBER01'])->count(),
            'total_transactions' => UnifiedTransaction::count(),
            'total_positions' => SlotPosition::count(),
        ];

        return view('admin.settings.index', compact('settings', 'stats'));
    }

    /**
     * Update Company and Platform Settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'software_name' => 'required|string|max:100',
            'company_email' => 'nullable|email|max:150',
            'support_email' => 'nullable|email|max:150',
            'company_phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'telegram_link' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'usdt_contract' => 'nullable|string|max:60',
            'company_reserve_wallet' => 'nullable|string|max:60',
            'company_revenue_wallet' => 'nullable|string|max:60',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        // If support_email was provided, sync to both keys for backward compatibility
        if ($request->filled('company_email') && !$request->filled('support_email')) {
            Setting::updateOrCreate(['key' => 'support_email'], ['value' => $request->company_email]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Company & platform settings have been updated successfully.');
    }

    /**
     * Safely Reset Software Database Data.
     *
     * Clears all test members, transactions, positions, and OTPs.
     * Strictly preserves ADMIN01, COMPANY01 (A), and MEMBER01 (B),
     * and automatically re-positions both across all 12 slots and recycle pools.
     */
    public function resetDatabase(Request $request)
    {
        $admin = adminUser() ?? Auth::guard('admin')->user() ?? Auth::user();
        if (!$admin) {
            return back()->with('error', 'Authentication failed: You must be logged in as an administrator.');
        }

        $request->validate([
            'admin_password' => 'required|string',
            'confirm_phrase' => 'required|string',
        ]);

        // Security Check 1: Admin Password Verification
        if (!Hash::check($request->admin_password, $admin->password)) {
            return back()->with('error', 'Authentication failed: Incorrect Admin password entered. Data reset was aborted.');
        }

        // Security Check 2: Exact Phrase Verification
        if (trim($request->confirm_phrase) !== 'RESET-DATABASE') {
            return back()->with('error', 'Confirmation failed: You must type exactly "RESET-DATABASE" to confirm the reset.');
        }

        try {
            DB::beginTransaction();
            Schema::disableForeignKeyConstraints();

            // 1. Truncate operational tables
            UnifiedTransaction::truncate();
            RecyclePosition::truncate();
            SlotPosition::truncate();
            UserOtp::truncate();

            // 2. Delete all members except the core 3 system accounts
            User::whereNotIn('member_id', ['ADMIN01', 'COMPANY01', 'MEMBER01'])->delete();

            // 3. Reset Company Root (COMPANY01 - Account A)
            $company = User::where('member_id', 'COMPANY01')->first();
            if ($company) {
                $company->update([
                    'name' => 'Company Root (A)',
                    'email' => 'company@nevora.com',
                    'sponsor_id' => null,
                    'parent_id' => null,
                    'parent_string' => (string) $company->id,
                    'role_id' => 2,
                    'is_paid' => true,
                    'current_slot_id' => 12,
                ]);
            }

            // 4. Reset Root Member (MEMBER01 - Account B)
            $memberB = User::where('member_id', 'MEMBER01')->first();
            if ($memberB && $company) {
                $memberB->update([
                    'name' => 'Root Member (B)',
                    'email' => 'member@nevora.com',
                    'sponsor_id' => $company->member_id,
                    'parent_id' => $company->member_id,
                    'parent_string' => $company->id . ',' . $memberB->id,
                    'role_id' => 2,
                    'is_paid' => true,
                    'current_slot_id' => 12,
                ]);
            }

            // 5. Pre-place both Account A and Account B across ALL 12 Dynamic Slots and Recycle Pools
            if ($company && $memberB) {
                $allSlots = Slot::orderBy('slot_number', 'asc')->get();

                foreach ($allSlots as $slot) {
                    // Place Company Root (A) at Root of this Slot
                    $cPos = SlotPosition::create([
                        'user_id' => $company->id,
                        'slot_id' => $slot->id,
                        'slot_number' => $slot->slot_number,
                        'parent_position_id' => null,
                        'child_position_ids' => [],
                        'leg_position' => 1,
                        'level' => 1,
                        'received_payments_count' => 0,
                        'is_completed' => false,
                    ]);

                    // Place Member B directly under Company Root (A) in this Slot
                    $bPos = SlotPosition::create([
                        'user_id' => $memberB->id,
                        'slot_id' => $slot->id,
                        'slot_number' => $slot->slot_number,
                        'parent_position_id' => $cPos->id,
                        'child_position_ids' => [],
                        'leg_position' => 1,
                        'level' => 2,
                        'received_payments_count' => 0,
                        'is_completed' => false,
                    ]);

                    // Update Company A's child array
                    $cPos->child_position_ids = [$bPos->id];
                    $cPos->save();

                    // Pre-place in Recycle Pool Cycle 1 if slot supports recycle
                    if ($slot->max_recycles > 0) {
                        $cRec = RecyclePosition::create([
                            'user_id' => $company->id,
                            'slot_id' => $slot->id,
                            'slot_number' => $slot->slot_number,
                            'cycle_number' => 1,
                            'parent_position_id' => null,
                            'child_position_ids' => [],
                            'leg_position' => 1,
                            'level' => 1,
                            'received_payments_count' => 0,
                            'is_completed' => false,
                        ]);

                        $bRec = RecyclePosition::create([
                            'user_id' => $memberB->id,
                            'slot_id' => $slot->id,
                            'slot_number' => $slot->slot_number,
                            'cycle_number' => 1,
                            'parent_position_id' => $cRec->id,
                            'child_position_ids' => [],
                            'leg_position' => 1,
                            'level' => 2,
                            'received_payments_count' => 0,
                            'is_completed' => false,
                        ]);

                        $cRec->child_position_ids = [$bRec->id];
                        $cRec->save();
                    }
                }
            }

            Schema::enableForeignKeyConstraints();
            DB::commit();

            // Clear system caches safely
            try {
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
            } catch (\Throwable $cacheEx) {
                // Ignore cache clearing errors if any
            }

            return redirect()->route('admin.settings.index')->with('success', 'Software data has been successfully reset! Test members and logs were purged, and both Company Root (A) and Member (B) have been re-initialized across all 12 pools.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            return back()->with('error', 'Database reset encountered an error: ' . $e->getMessage());
        }
    }
}
