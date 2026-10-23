<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEVORA Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b1120; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b1120; padding: 45px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container (White Card on Dark Background matching Project UI) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5); overflow: hidden; border: 1px solid #1e293b;">
                    
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 30px 20px; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #f1f5f9;">
                            @php
                                $emailLogoUrl = getSetting('email_logo_url');
                                if (!$emailLogoUrl) {
                                    $appUrl = config('app.url');
                                    if ($appUrl && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
                                        $emailLogoUrl = rtrim($appUrl, '/') . '/assets/images/logo-email.png';
                                    }
                                }

                                if (!$emailLogoUrl && isset($message) && method_exists($message, 'embedData')) {
                                    $logoPath = public_path('assets/images/logo-email.png');
                                    if (!file_exists($logoPath)) {
                                        $logoPath = public_path('assets/images/logo-main.png');
                                    }
                                    if (file_exists($logoPath)) {
                                        $bytes = @file_get_contents($logoPath);
                                        if ($bytes) {
                                            $emailLogoUrl = $message->embedData($bytes, 'logo.png', 'image/png');
                                        }
                                    }
                                }
                            @endphp

                            <div style="margin-bottom: 12px;">
                                @if(!empty($emailLogoUrl))
                                    <img src="{{ $emailLogoUrl }}" alt="NEVORA" style="max-height: 80px; max-width: 210px; width: auto; height: auto; display: block; margin: 0 auto; border: 0;" />
                                @else
                                    <div style="font-size: 26px; font-weight: 800; letter-spacing: 2px; color: #0f172a; text-transform: uppercase;">
                                        NEVORA
                                    </div>
                                @endif
                            </div>

                            <!-- Project Pill Badge -->
                            <div>
                                <span style="display: inline-block; background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 20px; letter-spacing: 1px; text-transform: uppercase;">
                                    Next-Gen Financial Ecosystem
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px 40px 25px;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; text-align: center; margin: 0 0 10px; letter-spacing: -0.3px;">
                                Security Verification Code
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; text-align: center; margin: 0 0 25px;">
                                @if(isset($actionType) && $actionType === 'wallet_binding')
                                    Please use the verification code below to authorize your wallet address binding on NEVORA.
                                @else
                                    Please use the verification code below to complete your authentication request on NEVORA.
                                @endif
                            </p>

                            <!-- OTP Box (Dark High-Contrast Pill inside White Card) -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 18px 42px; background-color: #0f172a; border: 2px dashed #2563eb; border-radius: 14px; text-align: center; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);">
                                            <span style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #38bdf8; display: inline-block; margin-right: -10px;">
                                                {{ $otpCode }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiration notice -->
                            <p style="font-size: 13px; color: #64748b; text-align: center; margin: 0 0 22px; line-height: 1.5;">
                                ⏳ This code is valid for <strong style="color: #0f172a;">15 minutes</strong> and can only be used once.
                            </p>

                            <!-- Security Warning Box -->
                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 12px 16px; margin: 0;">
                                <p style="font-size: 12px; color: #991b1b; margin: 0; line-height: 1.5;">
                                    <strong style="color: #b91c1c;">Security Notice:</strong> Never share this OTP with anyone, including NEVORA administrators. Our team will never ask for your verification code.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 22px 35px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 8px; line-height: 1.5;">
                                If you did not request this verification code, please ignore this email or reach us at
                                <a href="mailto:info@nevora.world" style="color: #2563eb; text-decoration: none; font-weight: 600;">info@nevora.world</a>.
                            </p>
                            <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                                &copy; {{ date('Y') }} NEVORA Platform. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
