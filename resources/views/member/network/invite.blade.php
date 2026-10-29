@extends('layouts.member')

@section('header_title', 'Invite Members & Grow Network')
@section('header_subtitle', 'Share your 3-matrix referral link to onboard new members directly into your team.')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Left Column: Referral Link & Sharing Tools --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-link font-22"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Your Unique Referral Link</h5>
                            <small class="text-muted">Bound to your Sponsor Member ID: <strong class="text-primary font-monospace">{{ $user->member_id }}</strong></small>
                        </div>
                    </div>

                    @if($user->is_paid)
                        {{-- Copy Link Group --}}
                        <div class="mb-4">
                            <label class="form-label font-12 fw-bold text-muted text-uppercase mb-1.5">Referral URL</label>
                            <div class="input-group">
                                <input type="text" id="invite-ref-link" class="form-control font-monospace font-13 bg-light" value="{{ $refLink }}" readonly>
                                <button class="btn btn-primary px-3 fw-bold" type="button" onclick="copyInviteLink()">
                                    <i class="fa-solid fa-copy me-1"></i> Copy Link
                                </button>
                            </div>
                            <div id="copySuccessMsg" class="font-12 text-success mt-1.5 d-none fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> Referral link copied to clipboard!
                            </div>
                        </div>

                        {{-- Instant Social Share Buttons --}}
                        <div class="mb-4">
                            <label class="form-label font-12 fw-bold text-muted text-uppercase mb-2">Instant Social Share</label>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://api.whatsapp.com/send?text={{ urlencode('Join my NEVORA 3-Matrix team! Click here to connect your Web3 wallet and register: ' . $refLink) }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 py-2 flex-grow-1 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-1.5">
                                    <i class="fa-brands fa-whatsapp font-15"></i> WhatsApp
                                </a>
                                <a href="https://t.me/share/url?url={{ urlencode($refLink) }}&text={{ urlencode('Join my NEVORA 3-Matrix team!') }}" target="_blank" class="btn btn-info text-white btn-sm rounded-pill px-3 py-2 flex-grow-1 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-1.5">
                                    <i class="fa-brands fa-telegram font-15"></i> Telegram
                                </a>
                                <a href="https://twitter.com/intent/tweet?text={{ urlencode('Build generational wealth with NEVORA 3-Matrix Network: ' . $refLink) }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3 py-2 flex-grow-1 fw-bold shadow-xs text-white d-flex align-items-center justify-content-center gap-1.5">
                                    <i class="fa-brands fa-x-twitter font-15"></i> Twitter
                                </a>
                                <a href="mailto:?subject=Invitation to join NEVORA Network&body={{ urlencode('Hi, I invite you to join my NEVORA 3-Matrix team. Click this link to register: ' . $refLink) }}" class="btn btn-secondary btn-sm rounded-pill px-3 py-2 flex-grow-1 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-1.5">
                                    <i class="fa-solid fa-envelope font-15"></i> Email
                                </a>
                            </div>
                        </div>

                        {{-- How Referral Works Callout --}}
                        <div class="p-3 bg-light rounded-4 border font-12 text-muted">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-info text-primary mt-0.5 font-14"></i>
                                <div>
                                    <strong class="text-dark d-block mb-1">How Automated Web3 Registration Works:</strong>
                                    Share this link with prospective downline members. When they open your link, your Sponsor ID (<span class="font-monospace text-primary fw-bold">{{ $user->member_id }}</span>) is verified automatically. They link their SafePal, Trust Wallet, or Web3 wallet in 1 click, enter their basic details, and are placed into your 3-Matrix working tree immediately.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 rounded-4 p-4 text-center my-3">
                            <i class="fa-solid fa-lock font-36 text-warning mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">Sponsor Privileges Inactive</h6>
                            <p class="text-muted font-12 mb-3">You must activate Slot 1 ($15 USDT) on your dashboard before your referral link becomes active to sponsor new downline members.</p>
                            <a href="{{ route('member.dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2 font-13 fw-bold">
                                Go to Dashboard to Activate
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: QR Code Card --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2.5 mb-3" style="width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-qrcode font-24"></i>
                </div>
                <h5 class="fw-bold mb-1 text-dark">Scan QR Code to Join</h5>
                <p class="text-muted font-12 mb-3">Scan with SafePal, Trust Wallet, or mobile camera</p>

                @if($user->is_paid)
                    <div class="p-3 bg-light rounded-4 border mb-3 shadow-xs">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($refLink) }}" alt="Referral QR Code" class="img-fluid rounded-3 shadow-sm bg-white p-2" style="max-width: 200px;">
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary font-11 px-3 py-1.5 rounded-pill fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i> Verified Sponsor: {{ $user->member_id }}
                    </span>
                @else
                    <div class="p-4 bg-light rounded-4 border text-muted font-12">
                        <i class="fa-solid fa-lock fa-2x mb-2 text-warning d-block"></i>
                        Activate Slot 1 to display your QR code
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 2: KPI Metrics --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3.5 d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-users font-18"></i>
                        </div>
                        <div>
                            <span class="text-muted font-11 text-uppercase fw-semibold d-block">Total Directs</span>
                            <h3 class="fw-bold mb-0 text-dark">{{ $directsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3.5 d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-user-check font-18"></i>
                        </div>
                        <div>
                            <span class="text-muted font-11 text-uppercase fw-semibold d-block">Active (Paid) Members</span>
                            <h3 class="fw-bold mb-0 text-success">{{ $activeDirectsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3.5 d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-clock font-18"></i>
                        </div>
                        <div>
                            <span class="text-muted font-11 text-uppercase fw-semibold d-block">Pending Activation</span>
                            <h3 class="fw-bold mb-0 text-warning">{{ max(0, $directsCount - $activeDirectsCount) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Recent Direct Referrals List --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user-group font-16"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark font-15">Recent Direct Referrals</h5>
                </div>
                <a href="{{ route('member.network.directs') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 font-12 fw-bold">
                    View All Directs <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="card-body p-0">
                @if($recentDirects && $recentDirects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 font-13">
                            <thead class="table-light font-11 text-uppercase text-muted">
                                <tr>
                                    <th class="ps-4">Member ID</th>
                                    <th>Full Name</th>
                                    <th>Linked Wallet</th>
                                    <th>Registered On</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDirects as $direct)
                                    <tr>
                                        <td class="ps-4 font-monospace fw-bold text-primary">{{ $direct->member_id }}</td>
                                        <td class="fw-semibold text-dark">{{ $direct->name }}</td>
                                        <td class="font-monospace text-muted font-12">
                                            {{ $direct->wallet_address ? maskAddress($direct->wallet_address) : 'Not Linked' }}
                                        </td>
                                        <td class="text-muted font-12">{{ $direct->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-center">
                                            @if($direct->is_paid)
                                                <span class="badge bg-success bg-opacity-10 text-success font-11 px-2.5 py-1 rounded-pill fw-bold">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Active (Slot {{ $direct->current_slot_id }})
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning font-11 px-2.5 py-1 rounded-pill fw-bold">
                                                    <i class="fa-solid fa-clock me-1"></i> Pending Activation
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-user-plus font-36 text-muted opacity-50 mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark font-14">No Referrals Yet</h6>
                        <p class="text-muted font-12 mb-0">Share your referral link or QR code above to start growing your 3-matrix team!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function copyInviteLink() {
    const input = document.getElementById('invite-ref-link');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const msg = document.getElementById('copySuccessMsg');
        if (msg) msg.classList.remove('d-none');
        if (typeof toastr !== 'undefined') {
            toastr.success('Referral link copied to clipboard!');
        } else if (typeof toastrMessage === 'function') {
            toastrMessage('Referral link copied to clipboard!', 'success');
        }
        setTimeout(() => {
            if (msg) msg.classList.add('d-none');
        }, 3000);
    });
}
</script>
@endsection
