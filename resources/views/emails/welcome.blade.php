<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NEVORA - Account Created</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b1120; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b1120; padding: 45px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5); overflow: hidden; border: 1px solid #1e293b;">
                    
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

                            <div>
                                <span style="display: inline-block; background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 20px; letter-spacing: 1px; text-transform: uppercase;">
                                    Welcome to NEVORA 3-Matrix Network
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px 40px 25px;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; text-align: center; margin: 0 0 10px; letter-spacing: -0.3px;">
                                Welcome Aboard, {{ $user->name }}! 🎉
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; text-align: center; margin: 0 0 25px;">
                                Your NEVORA account has been successfully created and linked with your decentralized Web3 wallet.
                            </p>

                            <!-- Member ID Hero Box -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 16px 36px; background-color: #0f172a; border: 2px solid #2563eb; border-radius: 14px; text-align: center; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; display: block; margin-bottom: 4px; font-weight: 700;">
                                                Your Member ID
                                            </span>
                                            <span style="font-family: 'Courier New', Courier, monospace; font-size: 30px; font-weight: 900; letter-spacing: 3px; color: #38bdf8;">
                                                {{ $user->member_id }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Account Details Table -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin: 0 0 25px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; width: 40%; font-weight: 600;">Full Name:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700;">{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Email Address:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 600;">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Mobile Number:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 600;">{{ $user->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Sponsor Member ID:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #2563eb; font-weight: 700; font-family: monospace;">{{ $user->sponsor_id ?? 'COMPANY01' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #64748b; font-weight: 600;">Linked BEP-20 Wallet:</td>
                                    <td style="padding: 12px 18px; font-size: 12px; color: #0f172a; font-family: monospace; font-weight: 600; word-break: break-all;">{{ $user->wallet_address }}</td>
                                </tr>
                            </table>

                            <!-- Activation Next Step Callout -->
                            <div style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%); border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 18px; margin: 0 0 25px; text-align: center;">
                                <h3 style="font-size: 15px; font-weight: 800; color: #1e40af; margin: 0 0 6px;">
                                    🚀 Next Step: Activate Slot 1 ($15.00 USDT)
                                </h3>
                                <p style="font-size: 13px; line-height: 1.5; color: #334155; margin: 0 0 16px;">
                                    Your account is currently in pending activation status. Complete the 1-click $15 USDT transfer from your connected Web3 wallet to activate your position and unlock 3-matrix peer-to-peer earnings.
                                </p>
                                <a href="{{ route('member.dashboard') }}" style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 12px 28px; border-radius: 50px; box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);">
                                    Go to Member Dashboard &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin: 0; line-height: 1.5;">
                                If you did not register for this account, please contact our support team immediately.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 30px; background-color: #f8fafc; border-top: 1px solid #f1f5f9;">
                            <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                                &copy; {{ date('Y') }} NEVORA Ecosystem. All rights reserved. Secure Decentralized Network.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

