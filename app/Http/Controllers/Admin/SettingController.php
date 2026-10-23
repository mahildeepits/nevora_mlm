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
use Illuminate\Support\Facades\Mail;

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

        $mailConfig = [
            'mailer' => config('mail.default', env('MAIL_MAILER', 'smtp')),
            'host' => config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.hostinger.com')),
            'port' => config('mail.mailers.smtp.port', env('MAIL_PORT', 465)),
            'username' => config('mail.mailers.smtp.username', env('MAIL_USERNAME', 'info@nevora.world')),
            'has_password' => !empty(env('MAIL_PASSWORD')),
            'from_address' => config('mail.from.address', env('MAIL_FROM_ADDRESS', 'info@nevora.world')),
            'from_name' => config('mail.from.name', env('MAIL_FROM_NAME', 'NEVORA')),
            'scheme' => config('mail.mailers.smtp.scheme', env('MAIL_SCHEME', 'smtps')),
        ];

        return view('admin.settings.index', compact('settings', 'stats', 'mailConfig'));
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
     * Update SMTP Mailer Configuration in .env
     */
    public function updateSmtp(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string|max:190',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|string|max:190',
            'mail_password' => 'nullable|string|max:190',
            'mail_from_address' => 'required|email|max:190',
            'mail_from_name' => 'required|string|max:100',
            'email_logo_url' => 'nullable|url|max:255',
        ]);

        if ($request->has('email_logo_url')) {
            Setting::updateOrCreate(
                ['key' => 'email_logo_url'],
                ['value' => $request->email_logo_url ?? '']
            );
        }

        $envUpdates = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_SCHEME' => ((int)$request->mail_port === 465) ? 'smtps' : 'smtp',
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => $request->mail_from_name,
        ];

        if ($request->filled('mail_password')) {
            $envUpdates['MAIL_PASSWORD'] = $request->mail_password;
        }

        $this->updateEnvFile($envUpdates);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $th) {}

        return redirect()->route('admin.settings.index', ['tab' => 'email'])->with('success', 'Hostinger SMTP settings updated successfully!');
    }

    /**
     * Send a Live Test Email to verify SMTP setup.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $targetEmail = $request->test_email;
        $otpSample = (string) mt_rand(100000, 999999);

        try {
            Mail::mailer('smtp')->send('emails.otp', [
                'otpCode' => $otpSample,
                'actionType' => 'test'
            ], function ($message) use ($targetEmail) {
                $message->to($targetEmail)
                        ->subject('NEVORA SMTP Test Verification Email');
            });

            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('success', "Test email was successfully sent to {$targetEmail} via Hostinger SMTP! Please check the recipient inbox.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.index', ['tab' => 'email'])
                ->with('error', "Failed to send email via SMTP: " . $e->getMessage());
        }
    }

    /**
     * Safely update key-value pairs in the .env file.
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $formattedValue = (str_contains((string)$value, ' ') || str_contains((string)$value, '#') || str_contains((string)$value, '$')) 
                ? '"' . addcslashes((string)$value, '"\\$') . '"' 
                : (string)$value;

            $pattern = "/^{$key}=.*/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$formattedValue}", $envContent);
            } else {
                $envContent .= "\n{$key}={$formattedValue}";
            }
        }

        file_put_contents($envPath, $envContent);
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
