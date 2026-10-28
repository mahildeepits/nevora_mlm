@extends('layouts.member')

@section('header_title', 'Invite Members & Grow Network')
@section('header_subtitle', 'Share your 3-matrix referral link or register new team downlines directly.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Left: Referral Tools & QR Code --}}
    <div class="col-lg-5 mb-4">
        {{-- Referral Link Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-link font-20"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Your Unique Referral Link</h5>
                        <small class="text-muted">Direct link bound to your Sponsor ID: <strong class="text-primary font-monospace">{{ $user->member_id }}</strong></small>
                    </div>
                </div>

                @if($user->is_paid)
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Copy & Share Link</label>
                        <div class="input-group">
                            <input type="text" id="invite-ref-link" class="form-control font-monospace" value="{{ $refLink }}" readonly>
                            <button class="btn btn-primary px-3" type="button" onclick="copyInviteLink()">
                                <i class="fa-solid fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <div id="copySuccessMsg" class="font-12 text-success mt-1 d-none fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> Referral link copied to clipboard!
                        </div>
                    </div>

                    {{-- QR Code --}}
                    <div class="text-center p-3 rounded-3 border my-3 bg-light">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($refLink) }}" alt="QR Code" class="img-fluid rounded-3 shadow-sm bg-white p-2">
                        <small class="text-muted d-block mt-2 font-11">Scan this QR code with mobile camera to register</small>
                    </div>

                    {{-- Social Share Buttons --}}
                    <label class="form-label small fw-bold text-muted mb-2">Instant Social Share</label>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://api.whatsapp.com/send?text={{ urlencode('Join my NEVORA 3-Matrix team! ' . $refLink) }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 flex-grow-1">
                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                        </a>
                        <a href="https://t.me/share/url?url={{ urlencode($refLink) }}&text={{ urlencode('Join NEVORA 3-Matrix Platform') }}" target="_blank" class="btn btn-info text-white btn-sm rounded-pill px-3 flex-grow-1">
                            <i class="fa-brands fa-telegram me-1"></i> Telegram
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode('Build generational wealth with NEVORA 3-Matrix: ' . $refLink) }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3 flex-grow-1 text-white">
                            <i class="fa-brands fa-x-twitter me-1"></i> Twitter
                        </a>
                        <a href="mailto:?subject=NEVORA Invitation&body={{ urlencode('Join my team here: ' . $refLink) }}" class="btn btn-secondary btn-sm rounded-pill px-3 flex-grow-1">
                            <i class="fa-solid fa-envelope me-1"></i> Email
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning border-0 rounded-3 mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-lock font-18"></i>
                            <div class="font-13">
                                <strong>Account Activation Required:</strong> You must activate Slot 1 before your referral link becomes active to sponsor members.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Business Plan PDF Presentation Card --}}
        <!-- <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(56, 189, 248, 0.25) !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="fa-solid fa-file-pdf font-24"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <h6 class="fw-bold mb-0 text-white font-14">Official Business Plan (PDF)</h6>
                            <span class="badge bg-primary-subtle text-primary font-10 rounded-pill">12 Slides</span>
                        </div>
                        <p class="text-white-50 font-11 mb-2 mt-1">Download and share the presentation deck with prospective leads & team members.</p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('public.presentation') }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 font-11 fw-bold">
                                <i class="fa-solid fa-eye me-1"></i> View Deck
                            </a>
                            <a href="{{ asset('docs/NEVORA_Business_Plan_Presentation.pdf') }}" download="NEVORA_Business_Plan_Presentation.pdf" class="btn btn-sm btn-warning text-dark rounded-pill px-3 font-11 fw-bold">
                                <i class="fa-solid fa-download me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        {{-- Directs KPI Cards --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 mx-auto mb-2" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-users font-16"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $directsCount }}</h4>
                        <small class="text-muted font-11">Total Directs</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 mx-auto mb-2" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-user-check font-16"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-success">{{ $activeDirectsCount }}</h4>
                        <small class="text-muted font-11">Active (Paid)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Direct Onboarding Form --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user-plus font-16"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Register Downline Member Directly</h5>
                        <small class="text-muted">Onboard new team members directly into your 3-Matrix working tree</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                @if($user->is_paid)
                    <form action="{{ route('member.invite.register') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Sponsor ID</label>
                                <input type="text" class="form-control font-monospace bg-light fw-bold text-primary" value="{{ $user->member_id }} ({{ $user->name }})" readonly disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter downline full name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="member@example.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Mobile / Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" placeholder="+1234567890" required>
                                @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold font-13">BEP-20 Wallet Address (BSC USDT) <small class="text-muted font-normal">(Optional, can be added later)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-wallet text-muted"></i></span>
                                    <input type="text" name="wallet_address" class="form-control font-monospace @error('wallet_address') is-invalid @enderror" placeholder="0x..." value="{{ old('wallet_address') }}">
                                    @error('wallet_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 6 characters" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold font-13">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                            </div>

                            <div class="col-12 pt-2">
                                <div class="p-3 bg-light rounded-3 font-12 text-muted">
                                    <i class="fa-solid fa-info-circle text-primary me-1"></i>
                                    Upon registration, this member will immediately be assigned a node position in your 3-matrix tree and will proceed to activate Slot 1 ($15).
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                                    <i class="fa-solid fa-user-plus me-1"></i> Register Member Into Tree
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-user-lock font-48 text-muted mb-3 opacity-50 d-block"></i>
                        <h6 class="fw-bold text-dark">Sponsor Privileges Locked</h6>
                        <p class="text-muted font-13 mb-3">Activate your account in Slot 1 to unlock downline member registration.</p>
                        <a href="{{ route('member.dashboard') }}" class="btn btn-primary rounded-pill px-4">
                            Activate Slot 1 Now
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function copyInviteLink() {
    const input = document.getElementById('invite-ref-link');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const msg = document.getElementById('copySuccessMsg');
        msg.classList.remove('d-none');
        if (typeof toastrMessage === 'function') {
            toastrMessage('Referral link copied to clipboard!', 'success');
        }
        setTimeout(() => msg.classList.add('d-none'), 3000);
    });
}
</script>
@endsection
