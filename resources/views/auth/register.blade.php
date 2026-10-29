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
                                <a href="{{ route('member.dashboard') }}" class="btn btn-outline-info btn-sm rounded-pill font-11 fw-bold px-3">
                                    Dashboard
                                </a>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('member.register') }}" id="register-form">
                            @csrf
                            <input type="hidden" name="wallet_address" id="wallet_address" value="">

                            <div class="row g-3">
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

                                {{-- Mobile --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Mobile Number <span class="text-danger">*</span></label>
                                    <div class="custom-input-group @error('mobile') has-error @enderror">
                                        <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                                        <input type="tel" name="mobile" id="mobile_input" class="form-control" placeholder="+1234567890" value="{{ old('mobile', $stashed['mobile'] ?? '') }}" required>
                                    </div>
                                    @error('mobile')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email Address (No OTP!) --}}
                                <div class="col-12">
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
                                    </div>
                                    @error('password')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6">
                                    <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="custom-input-group">
                                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation_input" class="form-control" placeholder="Re-enter password" value="{{ $stashed['password'] ?? '' }}" required>
                                    </div>
                                </div>

                                {{-- Web3 Environment Detection Notice --}}
                                <div class="col-12 pt-2">
                                    <div id="web3DetectedNotice" class="d-none alert alert-success border-0 rounded-4 p-2.5 mb-0 font-12 d-flex align-items-center gap-2 shadow-xs">
                                        <i class="fa-solid fa-circle-check text-success font-18"></i>
                                        <div>
                                            <strong id="web3DetectedTitle">Web3 Wallet Active</strong>
                                            <span class="d-block text-muted font-11">1-Click Automated Permission Ready. Tapping below will prompt your wallet to link automatically.</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Button: Triggers B TITAN Multi-Wallet Selector --}}
                                <div class="col-12 pt-2">
                                    <button type="button" id="btn-open-wallet-reg" class="btn btn-auth-submit w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="fa-solid fa-wallet"></i>
                                        <span>Proceed to Link Wallet & Register</span>
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
            const form = document.getElementById('register-form');
            const sponsorInput = document.getElementById('sponsor_id');
            const sponsorStatusBox = document.getElementById('sponsorStatusBox');
            const btnOpenWallet = document.getElementById('btn-open-wallet-reg');
            const walletInput = document.getElementById('wallet_address');

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

            // Validate Form Inputs before opening Web3 Modal
            function validateForm() {
                const sponsor = sponsorInput.value.trim();
                const name = document.getElementById('name_input').value.trim();
                const mobile = document.getElementById('mobile_input').value.trim();
                const email = document.getElementById('email_input').value.trim();
                const pass = document.getElementById('password_input').value;
                const passConfirm = document.getElementById('password_confirmation_input').value;

                if (!sponsor) {
                    toastr.warning('Please enter a valid Sponsor Member ID.');
                    sponsorInput.focus();
                    return false;
                }
                if (!name) {
                    toastr.warning('Please enter your full name.');
                    document.getElementById('name_input').focus();
                    return false;
                }
                if (!mobile) {
                    toastr.warning('Please enter your mobile number.');
                    document.getElementById('mobile_input').focus();
                    return false;
                }
                if (!email || !email.includes('@')) {
                    toastr.warning('Please enter a valid email address.');
                    document.getElementById('email_input').focus();
                    return false;
                }
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

            // Submit registration form via AJAX
            async function submitRegistrationForm() {
                try {
                    Swal.fire({
                        title: 'Creating Your NEVORA Account...',
                        html: `
                            <div class="my-3 text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <p class="font-12 text-muted mb-1">Linking wallet: <span class="font-monospace text-primary">${walletInput.value.slice(0, 6)}...${walletInput.value.slice(-4)}</span></p>
                            <small class="text-muted font-11">Generating Member ID and placing in 3-Matrix tree...</small>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                    });

                    const formData = new FormData(form);
                    const registerSubmitUrl = "{{ Route::has('member.register') ? route('member.register') : url('register') }}";
                    const submitRes = await fetch(registerSubmitUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    let data;
                    try {
                        data = await submitRes.json();
                    } catch (e) {
                        throw new Error('Server returned an unexpected response. Please verify details and try again.');
                    }

                    if (!submitRes.ok || !data.status) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Notice',
                            text: data.message || 'Could not complete registration. Please check your details.',
                            confirmButtonText: 'Review Details'
                        });
                        return;
                    }

                    // Differentiate between already logged in (sponsor session preserved) vs new guest
                    if (data.is_already_logged_in) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Member Registered Successfully! 🎉',
                            html: `
                                <p class="text-muted font-13 mb-3">${data.message}</p>
                                <div class="p-3 bg-light rounded-4 border text-start mb-3 font-12">
                                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                        <span class="text-muted">New Member ID:</span>
                                        <span class="fw-bold font-monospace text-primary font-14">${data.member_id}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                        <span class="text-muted">Full Name:</span>
                                        <span class="fw-bold text-dark">${data.name}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                        <span class="text-muted">Sponsor ID:</span>
                                        <span class="fw-bold font-monospace text-primary">${data.sponsor_id}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Linked Wallet:</span>
                                        <span class="fw-bold font-monospace text-dark">${data.wallet_address ? (data.wallet_address.slice(0, 6) + '...' + data.wallet_address.slice(-4)) : '-'}</span>
                                    </div>
                                </div>
                                <div class="p-2.5 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 mb-2 font-11 text-primary text-start">
                                    <i class="fa-solid fa-circle-check me-1"></i> Your current login session (<strong>${data.current_user_name || ''}</strong>) has been preserved.
                                </div>
                            `,
                            confirmButtonText: 'View My Directs',
                            confirmButtonColor: '#2563eb',
                            showCancelButton: true,
                            cancelButtonText: 'Stay Here'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ Route::has('member.network.directs') ? route('member.network.directs') : url('member/network/directs') }}";
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful! 🎉',
                            html: `
                                <p class="text-muted font-13 mb-3">${data.message}</p>
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="font-11 text-muted text-uppercase fw-semibold mb-1">Your Member ID</div>
                                    <h4 class="fw-bold font-monospace text-primary mb-0">${data.member_id || ''}</h4>
                                </div>
                                <small class="text-muted font-11 d-block">A welcome email has been sent to your inbox. Redirecting to dashboard to activate Slot 1...</small>
                            `,
                            confirmButtonText: 'Go to Dashboard',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.href = data.redirect || "{{ Route::has('member.dashboard') ? route('member.dashboard') : url('member/dashboard') }}";
                        });
                    }
                } catch (err) {
                    console.error('Registration submit error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Error',
                        text: err.message || 'Failed to complete registration.',
                        confirmButtonText: 'Try Again'
                    });
                }
            }

            // Check for injected Web3 provider on page load
            const detectedProvider = NevoraWeb3.resolveProvider();
            if (detectedProvider) {
                const notice = document.getElementById('web3DetectedNotice');
                if (notice) {
                    notice.classList.remove('d-none');
                    const titleEl = document.getElementById('web3DetectedTitle');
                    if (titleEl) {
                        if (window.safepalProvider || window.ethereum?.isSafePal) titleEl.textContent = 'SafePal Wallet Connected';
                        else if (window.trustwallet || window.ethereum?.isTrust) titleEl.textContent = 'Trust Wallet Connected';
                        else if (window.tokenpocket || window.ethereum?.isTokenPocket) titleEl.textContent = 'TokenPocket Connected';
                        else titleEl.textContent = 'Web3 DApp Wallet Connected';
                    }
                }
                const btnSpan = btnOpenWallet.querySelector('span');
                if (btnSpan) btnSpan.textContent = 'Authorize Permission & Register [1-Click]';
            }

            // B TITAN Automatic Handshake: Detect ?auto_connect parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            const autoConnectWallet = urlParams.get('auto_connect');
            if (autoConnectWallet) {
                // Give 400ms for wallet injection inside DApp browser
                setTimeout(async () => {
                    const prov = NevoraWeb3.resolveProvider(autoConnectWallet) || window.safepalProvider || window.ethereum;
                    if (prov) {
                        try {
                            Swal.fire({
                                title: `Connecting to ${autoConnectWallet.toUpperCase()}...`,
                                html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Authorizing connection in your wallet app...</p>',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                showCancelButton: true,
                                cancelButtonText: 'Cancel'
                            });

                            const res = await NevoraWeb3.connectByPlatform(autoConnectWallet);
                            if (res && res.address) {
                                walletInput.value = res.address;
                                await submitRegistrationForm();
                            }
                        } catch (err) {
                            console.warn('Auto-connect permission error:', err);
                            Swal.close();
                        }
                    }
                }, 400);
            }

            // Click Handler: Open B TITAN Wallet Selector Modal or Connect Injected Provider Directly
            btnOpenWallet.addEventListener('click', async function () {
                if (!validateForm()) return;

                // Scenario 1: Already inside Web3 DApp Browser (SafePal / Trust / etc.)
                const activeProvider = NevoraWeb3.resolveProvider();
                if (activeProvider) {
                    try {
                        Swal.fire({
                            title: 'Authorizing Wallet...',
                            html: '<i class="fa-solid fa-spinner fa-spin fa-2x text-primary my-3"></i><p class="font-12 text-muted">Please approve the connection permission in your wallet...</p>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            showCancelButton: true,
                            cancelButtonText: 'Cancel',
                        });

                        const activeWalletId = NevoraWeb3.getDetectedWalletId() || 'safepal';
                        const res = await NevoraWeb3.connectByPlatform(activeWalletId);
                        if (res && res.address) {
                            walletInput.value = res.address;
                            await submitRegistrationForm();
                        }
                    } catch (e) {
                        if (e.code !== 4001 && e.code !== 'ACTION_REJECTED') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Permission Declined',
                                text: e.message || 'Wallet connection was not approved.',
                                confirmButtonText: 'Try Again'
                            });
                        }
                    }
                    return;
                }

                // Scenario 2: Outside Web3 (Standard Mobile Chrome / Desktop) -> Genuine WalletConnect v2
                NevoraWeb3.showWalletModal({
                    title: 'Select Web3 Wallet Platform',
                    subtitle: 'Choose your wallet app to link with your NEVORA account and complete registration',
                    onSelect: async (walletId, wallet) => {
                        try {
                            const res = await NevoraWeb3.connectByPlatform(walletId);
                            if (res && res.address) {
                                walletInput.value = res.address;
                                await submitRegistrationForm();
                            }
                        } catch (err) {
                            console.error('Registration wallet connection error:', err);
                            if (err.code !== 4001 && err.code !== 'ACTION_REJECTED' && err.message !== 'Connection request reset') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Wallet Connection Notice',
                                    text: err.message || 'Failed to connect to selected wallet.',
                                    confirmButtonText: 'Try Again'
                                });
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
