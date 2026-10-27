@extends('layouts.member')

@section('header_title', 'My Profile & Settings')
@section('header_subtitle', 'Manage your personal details, BEP-20 wallet binding, and account security.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- User Summary Card --}}
    <div class="col-lg-4 col-xl-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="overflow: hidden;">
            <div class="card-body p-2">
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="rounded-circle img-fluid border border-3 border-primary shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill {{ $user->is_paid ? 'bg-success' : 'bg-warning text-dark' }} p-2">
                        <i class="fa-solid {{ $user->is_paid ? 'fa-check' : 'fa-clock' }}"></i>
                    </span>
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2"><span class="badge bg-soft-primary text-primary px-3 py-1 font-12">{{ $user->member_id }}</span></p>
                <div class="d-flex justify-content-center align-items-center gap-1 mb-3">
                    <span class="badge {{ $user->is_paid ? 'bg-success' : 'bg-secondary' }} px-3 py-1 rounded-pill">
                        {{ $user->is_paid ? 'Active (Slot ' . $user->current_slot_id . ')' : 'Unpaid Member' }}
                    </span>
                </div>

                <hr class="my-3 opacity-25">

                <div class="text-start">
                    <div class="mb-2">
                        <small class="text-muted d-block font-11 text-uppercase fw-bold">Email Address</small>
                        <span class="fw-medium font-13 text-break" style="word-break: break-word; overflow-wrap: anywhere;">{{ $user->email }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block font-11 text-uppercase fw-bold">Sponsor ID</small>
                        <span class="fw-medium font-13">{{ $user->sponsor_id ?? 'None (Company Root)' }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block font-11 text-uppercase fw-bold">Joined On</small>
                        <span class="fw-medium font-13">{{ $user->created_at->format('d M, Y h:i A') }}</span>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <small class="text-muted font-11 text-uppercase fw-bold">BEP-20 Wallet</small>
                            @if($user->wallet_address)
                                <button type="button" 
                                        class="btn btn-sm btn-link p-0 text-primary text-decoration-none font-11 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                        onclick="copyWalletAddress(this, '{{ $user->wallet_address }}')" 
                                        title="Copy full address to clipboard">
                                    <i class="fa-regular fa-copy font-11"></i>
                                    <span>Copy</span>
                                </button>
                            @endif
                        </div>
                        @if($user->wallet_address)
                            <div class="p-2.5 rounded-3 bg-light border border-light-subtle d-flex align-items-start gap-2 shadow-sm" style="overflow: hidden; max-width: 100%;">
                                <i class="fa-solid fa-wallet text-primary font-12 mt-1 flex-shrink-0"></i>
                                <span class="font-monospace font-11 text-dark" 
                                      style="word-break: break-all; overflow-wrap: anywhere; white-space: normal; line-height: 1.45; display: block; width: 100%;" 
                                      title="{{ $user->wallet_address }}">
                                    {{ $user->wallet_address }}
                                </span>
                            </div>
                        @else
                            <span class="badge bg-soft-danger text-danger font-11 px-2.5 py-1.5 rounded-pill">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Not Bound Yet
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Section --}}
    <div class="col-lg-8 col-xl-9">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header border-bottom bg-transparent pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold py-3 px-4" id="details-tab" data-bs-toggle="tab" href="#details" role="tab">
                            <i class="fa-solid fa-user-pen me-2"></i> Edit Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold py-3 px-4" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab">
                            <i class="fa-solid fa-wallet me-2"></i> Connected Wallet (BEP-20)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold py-3 px-4" id="avatar-tab" data-bs-toggle="tab" href="#avatar" role="tab">
                            <i class="fa-solid fa-camera me-2"></i> Profile Photo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold py-3 px-4" id="password-tab" data-bs-toggle="tab" href="#password" role="tab">
                            <i class="fa-solid fa-key me-2"></i> Change Password
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="profileTabsContent">

                    {{-- TAB 1: Edit Details --}}
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <form action="{{ route('member.profile.update') }}" method="POST">
                            @csrf
                            <h5 class="fw-bold mb-3 text-primary">Personal Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Email Address (Read Only)</label>
                                    <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                                    <small class="text-muted font-11">Email address cannot be changed.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Mobile / Phone Number</label>
                                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile) }}" placeholder="+1234567890">
                                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ old('country', $user->country) }}" placeholder="e.g. United States">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold font-13">Residential Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Street address">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">State / Province</label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state', $user->state) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">Zip / Postal Code</label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $user->pincode) }}">
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 2: BEP-20 Wallet Address Binding --}}
                    <div class="tab-pane fade" id="wallet" role="tabpanel">
                        <div class="alert alert-info border-0 rounded-3 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <i class="fa-solid fa-shield-halved font-24 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Decentralized Wallet Binding</h6>
                                    <p class="mb-0 font-13">
                                        All slot activations and direct matrix earnings are transferred directly on the Binance Smart Chain (BSC) in <strong>BEP-20 USDT</strong>.
                                        To ensure utmost security, binding or updating your BEP-20 address requires an Email OTP verification.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- 1-Click SafePal Connect Card --}}
                        <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-wallet font-18"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark font-13">Instant SafePal 1-Click Binding</h6>
                                    <p class="text-muted font-11 mb-0">Connect directly from your SafePal app or browser extension to bind without manual typing.</p>
                                </div>
                            </div>
                            <button type="button" onclick="NevoraWeb3.connectWallet(true)" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-bolt font-12 text-warning"></i> Connect & Auto-Bind SafePal
                            </button>
                        </div>

                        <form action="{{ route('member.profile.wallet.address') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold font-13">Current Linked BEP-20 Wallet</label>
                                    <input type="text" class="form-control bg-light font-monospace" value="{{ $user->wallet_address ?? 'Not Configured' }}" readonly disabled>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold font-13">New BEP-20 Wallet Address (BSC USDT) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-wallet text-primary"></i></span>
                                        <input type="text" name="wallet_address" id="wallet_address_input" class="form-control font-monospace web3-wallet-input @error('wallet_address') is-invalid @enderror" placeholder="0x..." value="{{ old('wallet_address') }}" required>
                                        @error('wallet_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <small class="text-muted font-11">Must be a valid 42-character BSC address starting with 0x.</small>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-bold font-13">Email Verification Code (OTP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-key text-warning"></i></span>
                                        <input type="text" name="otp" class="form-control @error('otp') is-invalid @enderror" placeholder="6-digit OTP code" required>
                                        <button type="button" class="btn btn-outline-primary" id="btnSendWalletOtp">
                                            <span id="btnOtpText"><i class="fa-solid fa-paper-plane me-1"></i> Send OTP</span>
                                            <span id="btnOtpSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                        @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div id="otpNotice" class="font-12 mt-1"></div>
                                </div>

                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-success px-4 rounded-pill">
                                        <i class="fa-solid fa-link me-1"></i> Bind Wallet Address
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 3: Profile Photo --}}
                    <div class="tab-pane fade" id="avatar" role="tabpanel">
                        <form action="{{ route('member.profile.avatar') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h5 class="fw-bold mb-3 text-primary">Update Profile Photo</h5>
                            <div class="row align-items-center g-4">
                                <div class="col-md-4 text-center">
                                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="rounded-circle border border-3 border-primary shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                                    <p class="text-muted small mt-2">Current Avatar</p>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold font-13">Select Image File (JPG, PNG, WEBP, max 2MB)</label>
                                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" required>
                                    @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill mt-3">
                                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Photo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 4: Change Password --}}
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <form action="{{ route('member.profile.password') }}" method="POST">
                            @csrf
                            <h5 class="fw-bold mb-3 text-primary">Security & Password</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold font-13">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                        <i class="fa-solid fa-key me-1"></i> Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSendOtp = document.getElementById('btnSendWalletOtp');
    const otpText = document.getElementById('btnOtpText');
    const otpSpinner = document.getElementById('btnOtpSpinner');
    const otpNotice = document.getElementById('otpNotice');

    if (btnSendOtp) {
        btnSendOtp.addEventListener('click', function() {
            btnSendOtp.disabled = true;
            otpSpinner.classList.remove('d-none');
            otpText.classList.add('d-none');
            otpNotice.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Sending OTP to your email...</span>';

            fetch("{{ route('member.profile.wallet.otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                btnSendOtp.disabled = false;
                otpSpinner.classList.add('d-none');
                otpText.classList.remove('d-none');

                if (data.status) {
                    otpNotice.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> ' + data.message + '</span>';
                    // 60-second cooldown
                    let cooldown = 60;
                    btnSendOtp.disabled = true;
                    const interval = setInterval(() => {
                        otpText.innerHTML = `<i class="fa-solid fa-clock me-1"></i> Resend (${cooldown}s)`;
                        cooldown--;
                        if (cooldown < 0) {
                            clearInterval(interval);
                            btnSendOtp.disabled = false;
                            otpText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send OTP';
                        }
                    }, 1000);
                } else {
                    otpNotice.innerHTML = '<span class="text-danger fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i> ' + (data.message || 'Failed to send OTP') + '</span>';
                }
            })
            .catch(err => {
                btnSendOtp.disabled = false;
                otpSpinner.classList.add('d-none');
                otpText.classList.remove('d-none');
                otpNotice.innerHTML = '<span class="text-danger fw-bold">Network error while requesting OTP.</span>';
            });
        });
    }
});

function copyWalletAddress(btn, address) {
    if (!address) return;
    const origHtml = btn.innerHTML;

    function showSuccess() {
        btn.innerHTML = '<i class="fa-solid fa-check text-success font-11"></i> <span class="text-success fw-bold">Copied!</span>';
        if (typeof toastrMessage === 'function') {
            toastrMessage('BEP-20 Wallet address copied!', 'success');
        }
        setTimeout(() => {
            btn.innerHTML = origHtml;
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(address)
            .then(showSuccess)
            .catch(() => fallbackCopy(address, showSuccess));
    } else {
        fallbackCopy(address, showSuccess);
    }
}

function fallbackCopy(text, onSuccess) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful && typeof onSuccess === 'function') {
            onSuccess();
        }
    } catch (e) {
        console.error('Fallback copy failed', e);
    }
    document.body.removeChild(textArea);
}
</script>
@endsection
