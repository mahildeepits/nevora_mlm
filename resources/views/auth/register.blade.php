<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register Account - {{ config('app.name', 'NEVORA') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <x-hlinks />
    <style>
        body {
            background: linear-gradient(135deg, #0b1120 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 30px 0;
        }
        .bg-glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.22) 0%, rgba(0, 0, 0, 0) 70%);
            top: -120px;
            right: -120px;
            border-radius: 50%;
            pointer-events: none;
        }
        .bg-glow-2 {
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            pointer-events: none;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .auth-card {
            background: rgba(21, 29, 48, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
        }
        .brand-icon-box {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        /* Seamless Custom Input Group (No border distortion) */
        .custom-input-group {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 14px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        .custom-input-group:focus-within {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
        }
        .custom-input-group.has-error {
            border-color: #dc3545 !important;
        }
        .custom-input-group .input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-left: 16px;
            font-size: 15px;
            color: #64748b;
        }
        .custom-input-group .form-control {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 12px 14px;
            font-size: 13.5px;
            color: #0f172a;
            border-radius: 0 !important;
            flex-grow: 1;
        }
        .custom-input-group .form-control:focus {
            box-shadow: none !important;
            outline: none !important;
        }
        .custom-input-group .btn-otp-action {
            margin: 4px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Dark mode inputs */
        [data-bs-theme="dark"] .custom-input-group {
            background: #1e293b;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .custom-input-group:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2) !important;
        }
        [data-bs-theme="dark"] .custom-input-group .form-control {
            color: #f8fafc;
        }
        [data-bs-theme="dark"] .custom-input-group .input-icon {
            color: #94a3b8;
        }

        .btn-auth-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            padding: 13px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 50rem;
            color: #ffffff !important;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
            transition: all 0.25s ease;
        }
        .btn-auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
        }
        .theme-switch-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="theme-switch-btn">
        <button type="button" onclick="toggleTheme()" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 shadow-sm d-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border-color: rgba(255, 255, 255, 0.2);">
            <i class="fa-solid fa-moon"></i>
            <span class="font-12">Theme</span>
        </button>
    </div>

    <div class="container py-4 position-relative" style="z-index: 5;">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <div class="card auth-card p-4 p-sm-5">
                    <div class="card-body p-0">
                        <div class="text-center mb-4">
                            <img src="{{ asset('assets/images/logo-main.png') }}" alt="NEVORA" class="img-fluid mb-2" style="max-height: 80px; object-fit: contain;">
                            <h4 class="fw-bold text-dark mb-1">Create Account</h4>
                            <p class="text-muted font-12 mb-0">Join the NEVORA 3-Matrix Decentralized Network</p>
                        </div>

                        <x-alerts />

                        <form method="POST" action="{{ route('member.register') }}" id="register-form">
                            @csrf

                            <div class="row g-3">
                                {{-- Sponsor ID --}}
                                <div class="col-12">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">
                                        Sponsor Member ID <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-input-group @error('sponsor_id') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-user-tag"></i></span>
                                        <input type="text" name="sponsor_id" id="sponsor_id" class="form-control font-monospace" placeholder="e.g. MEMBER01 or COMPANY01" value="{{ old('sponsor_id', $sponsorId ?? '') }}" required>
                                    </div>
                                    @if($sponsorName)
                                        <div class="font-12 text-success mt-1 fw-bold">
                                            <i class="fa-solid fa-circle-check me-1"></i> Sponsor Verified: {{ $sponsorName }}
                                        </div>
                                    @endif
                                    @error('sponsor_id')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Full Name <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('name') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" name="name" class="form-control" placeholder="Your full name" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Mobile --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Mobile Number <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('mobile') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                                        <input type="tel" name="mobile" class="form-control" placeholder="+1234567890" value="{{ old('mobile') }}" required>
                                    </div>
                                    @error('mobile')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email with Send OTP --}}
                                <div class="col-12">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Email Address <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('email') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="email" id="email_input" class="form-control" placeholder="name@domain.com" value="{{ old('email') }}" required>
                                        <button class="btn btn-outline-primary btn-otp-action" type="button" id="btnSendOtp">
                                            <span id="btnOtpText"><i class="fa-solid fa-paper-plane me-1"></i> Send OTP</span>
                                            <span id="btnOtpSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                    </div>
                                    <div id="otpNotice" class="font-12 mt-1"></div>
                                    <small class="text-muted font-11 d-block mt-1">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i> You can register multiple accounts with the same email. Each account requires a unique mobile number.
                                    </small>
                                    @error('email')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- OTP Input --}}
                                <div class="col-12">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Email Verification Code (OTP) <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('otp') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-key"></i></span>
                                        <input type="text" name="otp" class="form-control" placeholder="Enter 6-digit OTP code" required>
                                    </div>
                                    @error('otp')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Password --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Password <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('password') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                                    </div>
                                    @error('password')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="custom-input-group">
                                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                                    </div>
                                </div>

                                <div class="col-12 pt-3">
                                    <button type="submit" class="btn btn-auth-submit w-100">
                                        <i class="fa-solid fa-user-check me-2"></i> Complete Registration & Enter Tree
                                    </button>
                                </div>

                                <div class="col-12 text-center pt-2">
                                    <span class="text-muted font-13">Already have an account?</span>
                                    <a href="{{ route('member.login.form') }}" class="font-13 fw-bold text-primary ms-1 text-decoration-none">
                                        Sign In
                                    </a>
                                </div>

                                @if(getSetting('company_email') || getSetting('support_email'))
                                <div class="col-12 text-center pt-2 border-top mt-3">
                                    <small class="text-muted font-11">Need onboarding help? Contact support: 
                                        <a href="mailto:{{ getSetting('company_email') ?? getSetting('support_email') }}" class="text-primary text-decoration-none fw-semibold">
                                            {{ getSetting('company_email') ?? getSetting('support_email') }}
                                        </a>
                                    </small>
                                </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-fscripts />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnSendOtp = document.getElementById('btnSendOtp');
            const emailInput = document.getElementById('email_input');
            const btnOtpText = document.getElementById('btnOtpText');
            const btnOtpSpinner = document.getElementById('btnOtpSpinner');
            const otpNotice = document.getElementById('otpNotice');

            if (btnSendOtp) {
                btnSendOtp.addEventListener('click', function () {
                    const email = emailInput.value.trim();
                    if (!email) {
                        otpNotice.innerHTML = '<span class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i> Please enter your email address first.</span>';
                        return;
                    }

                    btnSendOtp.disabled = true;
                    btnOtpSpinner.classList.remove('d-none');
                    btnOtpText.classList.add('d-none');
                    otpNotice.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Sending OTP code...</span>';

                    fetch("{{ route('member.register.send_otp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: email })
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnSendOtp.disabled = false;
                        btnOtpSpinner.classList.add('d-none');
                        btnOtpText.classList.remove('d-none');

                        if (data.status) {
                            otpNotice.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> ' + data.message + '</span>';
                            let cooldown = 60;
                            btnSendOtp.disabled = true;
                            const interval = setInterval(() => {
                                btnOtpText.innerHTML = `<i class="fa-solid fa-clock me-1"></i> Resend (${cooldown}s)`;
                                cooldown--;
                                if (cooldown < 0) {
                                    clearInterval(interval);
                                    btnSendOtp.disabled = false;
                                    btnOtpText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send OTP';
                                }
                            }, 1000);
                        } else {
                            let errMessage = data.message || 'Failed to send OTP.';
                            if (data.errors && data.errors.email) {
                                errMessage = data.errors.email[0];
                            }
                            otpNotice.innerHTML = '<span class="text-danger fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i> ' + errMessage + '</span>';
                        }
                    })
                    .catch(err => {
                        btnSendOtp.disabled = false;
                        btnOtpSpinner.classList.add('d-none');
                        btnOtpText.classList.remove('d-none');
                        otpNotice.innerHTML = '<span class="text-danger fw-bold">Network error while requesting OTP.</span>';
                    });
                });
            }
        });
    </script>
</body>
</html>
