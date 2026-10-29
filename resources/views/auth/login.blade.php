<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Member Login - {{ config('app.name', 'NEVORA') }}</title>
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
            padding: 20px 0;
        }
        .bg-glow-1 {
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(0, 0, 0, 0) 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
            pointer-events: none;
        }
        .bg-glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(0, 0, 0, 0) 70%);
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
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 26px;
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
            padding: 13px 14px;
            font-size: 14px;
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
    </style>
</head>
<body>
    {{-- Decorative Ambient Glows --}}
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    {{-- Floating Theme Switcher --}}
    <div class="theme-switch-btn">
        <button type="button" onclick="toggleTheme()" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 shadow-sm d-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border-color: rgba(255, 255, 255, 0.2);">
            <i class="fa-solid fa-moon"></i>
            <span class="font-12">Theme</span>
        </button>
    </div>

    <div class="container py-4 position-relative" style="z-index: 5;">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card auth-card p-4 p-sm-5">
                    <div class="card-body p-0">
                        {{-- Brand Header --}}
                        <div class="text-center mb-4">
                            <img src="{{ asset('assets/images/logo-main.png') }}" alt="NEVORA" class="img-fluid mb-2" style="max-height: 85px; object-fit: contain;">
                            <p class="text-muted font-12 mb-2">3-Matrix Hybrid On-Chain Ecosystem</p>
                            <span class="badge bg-soft-primary px-3 py-1 rounded-pill font-11">
                                <i class="fa-solid fa-shield-halved me-1"></i> Decentralized Smart Network
                            </span>
                        </div>

                        {{-- Alert Messages (Auto-dismissed after 4 seconds) --}}
                        <x-alerts />

                        <form method="POST" action="{{ route('member.login') }}">
                            @csrf

                            {{-- Member ID --}}
                            <div class="mb-3">
                                <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Member ID</label>
                                <div class="custom-input-group @error('member_id') has-error @enderror">
                                    <span class="input-icon"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" name="member_id" class="form-control font-monospace" placeholder="e.g. NV100001 or COMPANY01" value="{{ old('member_id') }}" required autofocus>
                                </div>
                                @error('member_id')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Password --}}
                            <div class="mb-4">
                                <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1">Password</label>
                                <div class="custom-input-group @error('password') has-error @enderror">
                                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                    <button class="btn-eye-toggle" type="button" onclick="togglePasswordVisibility('password')" tabindex="-1">
                                        <i class="fa-solid fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-auth-submit w-100 mb-2">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Dashboard
                            </button>

                            <div class="position-relative text-center my-3">
                                <hr class="border-secondary border-opacity-25 my-0">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 font-11 text-muted fw-semibold text-uppercase">Or</span>
                            </div>

                            {{-- 1-Click Web3 Login (SafePal / Trust / MetaMask) --}}
                            <button type="button" onclick="loginWithWeb3Wallet()" class="btn btn-outline-primary w-100 rounded-pill py-2.5 font-13 fw-bold d-flex align-items-center justify-content-center gap-2 mb-3 shadow-xs">
                                <i class="fa-solid fa-wallet text-primary"></i>
                                <span>1-Click Web3 Wallet Sign In</span>
                            </button>

                            {{-- Register Link --}}
                            <div class="text-center pt-2">
                                <span class="text-muted font-13">New to NEVORA?</span>
                                <a href="{{ route('member.register.form') }}" class="font-13 fw-bold text-primary ms-1 text-decoration-none">
                                    Create Free Account
                                </a>
                            </div>

                            <div class="text-center mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <a href="{{ route('admin.login.form') }}" class="text-muted font-11 text-decoration-none d-inline-flex align-items-center gap-1 opacity-75">
                                    <i class="fa-solid fa-shield-halved"></i> Admin Portal
                                </a>
                                @if(getSetting('company_email') || getSetting('support_email'))
                                    <a href="mailto:{{ getSetting('company_email') ?? getSetting('support_email') }}" class="text-muted font-11 text-decoration-none d-inline-flex align-items-center gap-1 opacity-75">
                                        <i class="fa-solid fa-headset"></i> Support
                                    </a>
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
        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const eye = document.getElementById(id + '-eye');
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

        function loginWithWeb3Wallet() {
            NevoraWeb3.showWalletModal({
                title: 'Login with Web3 Wallet',
                subtitle: 'Select your wallet to sign in instantly without password',
                onSelect: async (walletId, wallet) => {
                    try {
                        const res = await NevoraWeb3.connectByPlatform(walletId);
                        if (!res || !res.address) return;

                        Swal.fire({
                            title: 'Authenticating...',
                            html: '<div class="spinner-border text-primary my-3" role="status"></div><p class="font-12 text-muted">Checking NEVORA member record...</p>',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                        });

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const response = await fetch("{{ Route::has('member.login.web3') ? route('member.login.web3') : url('login-web3') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ wallet_address: res.address })
                        });

                        const data = await response.json();
                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Welcome Back!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = data.redirect || "{{ Route::has('member.dashboard') ? route('member.dashboard') : url('member/dashboard') }}";
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Account Not Found',
                                text: data.message || 'No account linked with this wallet.',
                                confirmButtonText: 'Register Now',
                                showCancelButton: true,
                                cancelButtonText: 'Close'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ Route::has('member.register.form') ? route('member.register.form') : url('register') }}";
                                }
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: err.message || 'Could not connect to wallet.',
                        });
                    }
                }
            });
        }
    </script>
</body>
</html>
