<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEVORA Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b1120; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b1120; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background: linear-gradient(180deg, #131c31 0%, #0f172a 100%); border-radius: 18px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 35px 25px 20px; background: radial-gradient(circle at center, rgba(37, 99, 235, 0.25) 0%, rgba(19, 28, 49, 0) 70%);">
                            <div style="font-size: 26px; font-weight: 800; letter-spacing: 3px; color: #ffffff; text-transform: uppercase;">
                                <span style="background: linear-gradient(135deg, #38bdf8 0%, #3b82f6 50%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">NEVORA</span>
                            </div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #94a3b8; margin-top: 5px;">
                                Next-Gen Financial Ecosystem
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 10px 35px 30px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #ffffff; text-align: center; margin: 0 0 15px;">
                                Security Verification Code
                            </h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #cbd5e1; text-align: center; margin: 0 0 25px;">
                                @if(isset($actionType) && $actionType === 'wallet_binding')
                                    Please use the verification code below to authorize your wallet address binding on NEVORA.
                                @else
                                    Please use the verification code below to complete your authentication request on NEVORA.
                                @endif
                            </p>

                            <!-- OTP Box -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 16px 36px; background: rgba(15, 23, 42, 0.85); border: 2px dashed #3b82f6; border-radius: 12px; text-align: center;">
                                            <span style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 800; letter-spacing: 10px; color: #38bdf8; display: inline-block; margin-right: -10px;">
                                                {{ $otpCode }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiration notice -->
                            <p style="font-size: 13px; color: #94a3b8; text-align: center; margin: 0 0 20px; line-height: 1.5;">
                                ⏳ This code is valid for <strong>15 minutes</strong> and can only be used once.
                            </p>

                            <!-- Security Warning Box -->
                            <div style="background: rgba(239, 68, 68, 0.1); border-left: 3px solid #ef4444; border-radius: 6px; padding: 12px 16px; margin: 0 0 10px;">
                                <p style="font-size: 12px; color: #fca5a5; margin: 0; line-height: 1.5;">
                                    <strong>Security Notice:</strong> Never share this OTP with anyone, including NEVORA administrators. Our team will never ask for your verification code.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 22px 35px; background: rgba(11, 17, 32, 0.6); border-top: 1px solid rgba(255, 255, 255, 0.08); text-align: center;">
                            <p style="font-size: 11px; color: #64748b; margin: 0 0 8px; line-height: 1.5;">
                                If you did not request this verification code, please ignore this email or reach us immediately at
                                <a href="mailto:info@nevora.world" style="color: #38bdf8; text-decoration: none;">info@nevora.world</a>.
                            </p>
                            <p style="font-size: 11px; color: #475569; margin: 0;">
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
