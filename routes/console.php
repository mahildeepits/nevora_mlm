<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : The recipient email address}', function () {
    $email = $this->argument('email') ?? config('mail.from.address') ?? 'info@nevora.world';
    $this->info("Sending test email to {$email} using Host: " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port') . "...");
    
    try {
        $code = (string) mt_rand(100000, 999999);
        Mail::mailer('smtp')->send('emails.otp', ['otpCode' => $code, 'actionType' => 'test'], function ($message) use ($email) {
            $message->to($email)->subject('NEVORA SMTP Test Verification Email');
        });
        $this->info("SUCCESS: Test email was sent successfully to {$email}!");
    } catch (\Throwable $e) {
        $this->error("FAILED: Could not send email via SMTP.");
        $this->error("Error message: " . $e->getMessage());
    }
})->purpose('Send a test email using configured SMTP settings');

