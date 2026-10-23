<?php

namespace App\Services;

use App\Models\UserOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate and send OTP via email
     */
    public function generateAndSend(string $email, string $actionType = 'registration'): array
    {
        // Invalidate any existing unused OTPs for this email and action
        UserOtp::where('email', $email)
            ->where('action_type', $actionType)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate 6-digit numeric OTP
        $otpCode = (string) mt_rand(100000, 999999);

        $otp = UserOtp::create([
            'email' => $email,
            'otp' => $otpCode,
            'action_type' => $actionType,
            'is_used' => false,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send via Laravel Mail (using branded HTML email template)
        try {
            Mail::send('emails.otp', ['otpCode' => $otpCode, 'actionType' => $actionType], function ($message) use ($email) {
                $message->to($email)
                        ->subject('NEVORA Verification Code');
            });
        } catch (\Throwable $th) {
            Log::info("OTP for {$email} ({$actionType}): {$otpCode}. Mail sending skipped/logged: " . $th->getMessage());
        }

        return [
            'status' => true,
            'message' => 'Verification code sent to your email.',
            'otp' => app()->environment('local') ? $otpCode : null, // expose in local for ease of testing
        ];
    }

    /**
     * Validate OTP
     */
    public function verify(string $email, string $otpCode, string $actionType = 'registration'): bool
    {
        $otp = UserOtp::where('email', $email)
            ->where('otp', $otpCode)
            ->where('action_type', $actionType)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($otp) {
            $otp->update(['is_used' => true]);
            return true;
        }

        return false;
    }
}
