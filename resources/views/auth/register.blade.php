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

        /* Seamless Custom Input Group */
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
        .custom-input-group .btn-eye-toggle {
            background: transparent;
            border: none;
            padding: 0 16px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            outline: none;
            box-shadow: none;
            transition: color 0.2s ease;
        }
        .custom-input-group .btn-eye-toggle:hover {
            color: #0f172a;
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
        [data-bs-theme="dark"] .custom-input-group .input-icon,
        [data-bs-theme="dark"] .custom-input-group .btn-eye-toggle {
            color: #94a3b8;
        }
        [data-bs-theme="dark"] .custom-input-group .btn-eye-toggle:hover {
            color: #f8fafc;
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

        /* Wallet Option Card Hover */
        .wallet-option-card:hover {
            border-color: #2563eb !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
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

                        @if(isset($loggedInUser) && $loggedInUser)
                            <div class="alert alert-info border-0 rounded-4 mb-3 p-3 font-12 shadow-xs d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-user font-18 text-info"></i>
                                    <div>
                                        Signed in as <strong class="text-dark">{{ $loggedInUser->name }}</strong> (<span class="font-monospace text-primary fw-bold">{{ $loggedInUser->member_id }}</span>).
                                        <span class="d-block text-muted font-11">Registering a new downline will place them under this sponsor and preserve your current login session.</span>
                                    </div>
                                </div>
                                <a href="{{ Route::has('member.dashboard') ? route('member.dashboard') : url('member/dashboard') }}" class="btn btn-outline-info btn-sm rounded-pill font-11 fw-bold px-3">
                                    Dashboard
                                </a>
                            </div>
                        @endif

                        <form method="POST" action="{{ Route::has('member.register') ? route('member.register') : url('register') }}" id="register-form">
                            @csrf
                            <input type="hidden" name="wallet_address" id="wallet_address" value="">

                            <div class="row g-3">
                                {{-- Step 1: Connect Web3 Wallet (B-TITAN Authentic Flow) --}}
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label font-12 fw-bold text-muted text-uppercase mb-0">
                                            Connect Web3 Wallet <span class="text-danger">*</span>
                                        </label>
                                        <span class="badge bg-primary bg-opacity-10 text-primary font-10 px-2 py-0.5 rounded-pill">
                                            <i class="fa-solid fa-link me-1"></i> SafePal / Trust / Web3
                                        </span>
                                    </div>

                                    {{-- State A: Disconnected --}}
                                    <div id="wallet-connect-idle" class="w-100">
                                        <div class="p-2.5 rounded-4 border d-flex align-items-center justify-content-between flex-wrap gap-2" style="border: 1.5px dashed #3b82f6 !important; background: rgba(59, 130, 246, 0.04);">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div id="top-wallet-idle-icon-box" class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-wallet font-18"></i>
                                                </div>
                                                <div>
                                                    <span class="font-13 fw-bold text-dark d-block" id="top-wallet-idle-title">No Wallet Connected</span>
                                                    <small class="text-muted font-11" id="top-wallet-idle-subtitle">Click connect to link your SafePal or Web3 wallet</small>
                                                </div>
                                            </div>
                                            <button type="button" id="btn-top-connect-wallet" class="btn btn-primary rounded-pill px-3 py-1.5 font-12 fw-bold shadow-sm d-flex align-items-center gap-1.5 ms-auto">
                                                <i class="fa-solid fa-plug"></i> <span id="top-wallet-btn-label">Connect Wallet</span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- State B: Connected (B-TITAN Authentic Active Pill) --}}
                                    <div id="wallet-connect-active" class="w-100 d-none">
                                        <div class="p-2.5 rounded-4 border d-flex align-items-center justify-content-between shadow-xs flex-wrap gap-2" style="background: rgba(16, 185, 129, 0.06); border: 1.5px solid #10b981 !important;">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div id="top-wallet-icon-box" class="rounded-circle bg-white shadow-xs p-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-circle-check text-success font-20"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-1.5">
                                                        <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"></span>
                                                        <span class="font-13 fw-bold text-dark font-monospace" id="top-wallet-address-display">0x00...0000</span>
                                                        <span class="badge bg-success bg-opacity-10 text-success font-10 px-2 py-0.5 rounded-pill" id="top-wallet-platform-badge">Linked</span>
                                                    </div>
                                                    <small class="text-muted font-11 d-block">
                                                        <i class="fa-solid fa-network-wired text-primary me-1"></i> <span id="top-wallet-network-display">BNB Smart Chain (BEP-20)</span>
                                                    </small>
                                                </div>
                                            </div>
                                            <button type="button" id="btn-top-disconnect-wallet" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 font-11 ms-auto" title="Disconnect / Switch Wallet">
                                                <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Change
                                            </button>
                                        </div>
                                    </div>

                                    <div id="wallet-connect-error" class="text-danger font-11 mt-1.5 d-none fw-semibold">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i> Please connect your SafePal or Web3 wallet before submitting the registration form.
                                    </div>

                                    @error('wallet_address')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Sponsor ID --}}
                                <div class="col-12">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">
                                        Sponsor Member ID <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-input-group @error('sponsor_id') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-user-tag"></i></span>
                                        <input type="text" name="sponsor_id" id="sponsor_id" class="form-control font-monospace" placeholder="e.g. NV100001 or COMPANY01" value="{{ old('sponsor_id', $stashed['sponsor_id'] ?? ($sponsorId ?? '')) }}" required>
                                    </div>
                                    <div id="sponsorStatusBox" class="mt-1 font-12 fw-bold">
                                        @if($sponsorName)
                                            <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Sponsor Verified: {{ $sponsorName }}</span>
                                        @endif
                                    </div>
                                    @error('sponsor_id')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Full Name <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('name') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" name="name" id="name_input" class="form-control" placeholder="Your full name" value="{{ old('name', $stashed['name'] ?? '') }}" required>
                                    </div>
                                    @error('name')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email Address (No OTP!) --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Email Address <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('email') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="email" id="email_input" class="form-control" placeholder="name@domain.com" value="{{ old('email', $stashed['email'] ?? '') }}" required>
                                    </div>
                                    <small class="text-muted font-11 d-block mt-1">
                                        <i class="fa-solid fa-envelope-circle-check text-primary me-1"></i> A welcome email with your Member ID and account details will be sent here upon registration.
                                    </small>
                                    @error('email')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Password --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Password <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('password') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password" id="password_input" class="form-control" placeholder="Min 6 characters" value="{{ $stashed['password'] ?? '' }}" required>
                                        <button class="btn-eye-toggle" type="button" onclick="togglePasswordVisibility('password_input', 'password_eye')" tabindex="-1" aria-label="Toggle password visibility">
                                            <i class="fa-solid fa-eye" id="password_eye"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="custom-input-group">
                                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation_input" class="form-control" placeholder="Re-enter password" value="{{ $stashed['password'] ?? '' }}" required>
                                        <button class="btn-eye-toggle" type="button" onclick="togglePasswordVisibility('password_confirmation_input', 'password_confirmation_eye')" tabindex="-1" aria-label="Toggle confirm password visibility">
                                            <i class="fa-solid fa-eye" id="password_confirmation_eye"></i>
                                        </button>
                                    </div>
                                </div>



                                {{-- Submit Button: Triggers Atomic Pay-to-Register Flow --}}
                                <div class="col-12 pt-2">
                                    <button type="button" id="btn-submit-pay-register" class="btn btn-auth-submit w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="fa-solid fa-bolt text-warning font-16"></i>
                                        <span class="fw-bold font-15">Pay $15.00 USDT & Register</span>
                                    </button>
                                    <div class="text-center mt-2 text-muted font-11">
                                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Instant SafePal / Web3 Authorization • Verified on BNB Smart Chain
                                    </div>
                                </div>

                                <div class="col-12 text-center pt-2">
                                    <span class="text-muted font-13">Already have an account?</span>
                                    <a href="{{ Route::has('member.login.form') ? route('member.login.form') : url('login') }}" class="font-13 fw-bold text-primary ms-1 text-decoration-none">
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
            const form = document.getElementById('register-form');
            const sponsorInput = document.getElementById('sponsor_id');
            const sponsorStatusBox = document.getElementById('sponsorStatusBox');
            const walletInput = document.getElementById('wallet_address');
            const btnTopConnect = document.getElementById('btn-top-connect-wallet');
            const btnTopDisconnect = document.getElementById('btn-top-disconnect-wallet');
            const btnSubmitPay = document.getElementById('btn-submit-pay-register');

            // Prevent raw enter-key submit without Web3 payment authorization
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (btnSubmitPay) btnSubmitPay.click();
                });
            }

            // Dynamic Sponsor Live Lookup with Debounce
            let sponsorTimer = null;
            sponsorInput.addEventListener('input', function () {
                const val = this.value.trim();
                clearTimeout(sponsorTimer);
                if (!val) {
                    sponsorStatusBox.innerHTML = '';
                    return;
                }

                sponsorTimer = setTimeout(() => {
                    fetch(`{{ Route::has('member.check_sponsor') ? route('member.check_sponsor') : url('check-sponsor') }}?sponsor_id=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status) {
                                sponsorStatusBox.innerHTML = `<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Sponsor Verified: ${data.sponsor_name}</span>`;
                            } else {
                                sponsorStatusBox.innerHTML = `<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> ${data.message}</span>`;
                            }
                        })
                        .catch(() => {
                            sponsorStatusBox.innerHTML = '';
                        });
                }, 400);
            });

            function showWalletConnectError() {
                const errorEl = document.getElementById('wallet-connect-error');
                const idleBoxInner = document.querySelector('#wallet-connect-idle > div');
                if (errorEl) errorEl.classList.remove('d-none');
                if (idleBoxInner) {
                    idleBoxInner.style.setProperty('border', '1.5px dashed #dc2626', 'important');
                    idleBoxInner.style.backgroundColor = 'rgba(220, 38, 38, 0.05)';
                }
                document.getElementById('wallet-connect-idle')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function clearWalletConnectError() {
                const errorEl = document.getElementById('wallet-connect-error');
                const idleBoxInner = document.querySelector('#wallet-connect-idle > div');
                if (errorEl) errorEl.classList.add('d-none');
                if (idleBoxInner) {
                    idleBoxInner.style.setProperty('border', '1.5px dashed #3b82f6', 'important');
                    idleBoxInner.style.backgroundColor = 'rgba(59, 130, 246, 0.04)';
                }
            }

            // Validate Form Inputs before submitting
            function validateForm() {
                const currentWallet = walletInput.value.trim() || NevoraWeb3.getState().connectedAddress;
                const sponsor = sponsorInput.value.trim();
                const name = document.getElementById('name_input').value.trim();
                const email = document.getElementById('email_input').value.trim();
                const pass = document.getElementById('password_input').value;
                const passConfirm = document.getElementById('password_confirmation_input').value;

                // 1. Mandatory Step: Web3 Wallet MUST be connected first before registration
                if (!currentWallet) {
                    toastr.warning('Please connect your Web3 wallet first before submitting the form.');
                    showWalletConnectError();
                    return false;
                }
                clearWalletConnectError();

                // 2. Sponsor ID
                if (!sponsor) {
                    toastr.warning('Please enter a valid Sponsor Member ID.');
                    sponsorInput.focus();
                    return false;
                }
                // 3. Name
                if (!name) {
                    toastr.warning('Please enter your full name.');
                    document.getElementById('name_input').focus();
                    return false;
                }
                // 4. Email
                if (!email || !email.includes('@')) {
                    toastr.warning('Please enter a valid email address.');
                    document.getElementById('email_input').focus();
                    return false;
                }
                // 5. Password
                if (!pass || pass.length < 6) {
                    toastr.warning('Password must be at least 6 characters.');
                    document.getElementById('password_input').focus();
                    return false;
                }
                if (pass !== passConfirm) {
                    toastr.warning('Password and Confirm Password do not match.');
                    document.getElementById('password_confirmation_input').focus();
                    return false;
                }

                return true;
            }

            // Ensure clean disconnected state on fresh registration load (never auto-connect by default)
            NevoraWeb3.updateRegistrationTopBar(null);

            // Top Button: Connect Wallet Handlers (B-TITAN Authentic Flow - Opens Platforms Drawer)
            if (btnTopConnect) {
                btnTopConnect.addEventListener('click', function () {
                    NevoraWeb3.showWalletModal({
                        title: 'Connect Web3 Wallet',
                        subtitle: 'Select your wallet app to link with NEVORA',
                        onSelect: async (walletId, wallet) => {
                            Swal.fire({
                                title: `Connecting to ${wallet.name}...`,
                                html: `
                                    <div class="my-3 text-center">
                                        <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem;"></div>
                                    </div>
                                    <p class="font-13 text-muted mb-2">Please approve the connection in <strong>${wallet.name}</strong>.</p>
                                    <small class="text-muted font-11"><i class="fa-solid fa-fingerprint me-1"></i> Check your wallet extension or mobile app to approve</small>
                                `,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                showCancelButton: true,
                                cancelButtonText: 'Cancel'
                            });

                            try {
                                const res = await NevoraWeb3.connectByPlatform(walletId);
                                if (res && res.address) {
                                    Swal.close();
                                    clearWalletConnectError();
                                    NevoraWeb3.updateRegistrationTopBar(res.address, wallet.name, wallet.iconSvg);
                                    toastr.success(`${wallet.name} connected successfully!`);
                                }
                            } catch (e) {
                                if (e.code === 4001 || e.code === 'ACTION_REJECTED' || e.message?.includes('rejected') || e.message?.includes('denied')) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Connection Cancelled',
                                        text: `You cancelled the connection request in ${wallet.name}.`,
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Connection Failed',
                                        text: e.message || `Could not connect to ${wallet.name}.`,
                                        confirmButtonText: 'Try Again'
                                    });
                                }
                            }
                        }
                    });
                });
            }

            // Top Button: Disconnect / Change Wallet
            if (btnTopDisconnect) {
                btnTopDisconnect.addEventListener('click', async function () {
                    await NevoraWeb3.disconnectRegistrationWallet();
                    clearWalletConnectError();
                    toastr.info('Wallet disconnected. Click Connect Wallet to switch accounts.');
                });
            }

            // Click Handler: Triggers Atomic Pay-to-Register Flow
            if (btnSubmitPay) {
                btnSubmitPay.addEventListener('click', async function () {
                    // Strict validation: Web3 wallet MUST be connected before form submission
                    if (!validateForm()) return;

                    const formInputs = {
                        sponsor_id: sponsorInput.value.trim(),
                        name: document.getElementById('name_input').value.trim(),
                        email: document.getElementById('email_input').value.trim(),
                        password: document.getElementById('password_input').value,
                        password_confirmation: document.getElementById('password_confirmation_input').value
                    };

                    await NevoraWeb3.executePayToRegister(formInputs);
                });
            }
        });

        function togglePasswordVisibility(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId || (inputId + '-eye'));
            if (!input || !eye) return;
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
