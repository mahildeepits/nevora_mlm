@extends('layouts.member')

@section('title', 'Dashboard - NEVORA')
@section('header_title', 'Member Dashboard')
@section('header_subtitle', 'Welcome back, ' . $user->name . ' (' . $user->member_id . ')')

@section('header_right')
    <div class="px-3 py-2 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-25 text-white shadow-sm">
        <span class="d-block text-white-50 text-uppercase fw-semibold" style="font-size: 9px; letter-spacing: 0.5px;">Current Status</span>
        <h6 class="mb-0 text-white fw-bold" style="font-size: 13px;">
            @if($user->is_paid)
                <i class="fa-solid fa-circle-check text-success me-1"></i> Slot {{ $user->current_slot_id }} Active
            @else
                <i class="fa-solid fa-clock text-warning me-1"></i> Unpaid / Inactive
            @endif
        </h6>
    </div>
@endsection

@section('content')
    <x-alerts />

    @if(!$user->is_paid)
        {{-- ========================================================
             NEW USER / UNPAID ONBOARDING EXPERIENCE
             ======================================================== --}}
        
        {{-- 1. Onboarding Stepper Ribbon --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-3 p-md-4 bg-light bg-opacity-50">
                <div class="row g-3 align-items-center">
                    {{-- Step 1 --}}
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; min-width: 38px;">
                                <i class="fa-solid fa-check font-14"></i>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill font-10 fw-bold">STEP 1 COMPLETE</span>
                                <h6 class="mb-0 fw-bold text-dark font-13 mt-0.5">Account Registered</h6>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Step 2 --}}
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle {{ $user->wallet_address ? 'bg-success text-white' : 'bg-primary text-white' }} d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; min-width: 38px;">
                                @if($user->wallet_address)
                                    <i class="fa-solid fa-check font-14"></i>
                                @else
                                    <span class="fw-bold font-14">2</span>
                                @endif
                            </div>
                            <div>
                                @if($user->wallet_address)
                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill font-10 fw-bold">STEP 2 COMPLETE</span>
                                    <h6 class="mb-0 fw-bold text-dark font-13 mt-0.5">Wallet Linked</h6>
                                @else
                                    <span class="badge bg-primary-subtle text-primary px-2 py-0.5 rounded-pill font-10 fw-bold">STEP 2 REQUIRED</span>
                                    <h6 class="mb-0 fw-bold text-dark font-13 mt-0.5">Connect BEP-20 Wallet</h6>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; min-width: 38px;">
                                <i class="fa-solid fa-bolt font-14"></i>
                            </div>
                            <div>
                                <span class="badge bg-warning-subtle text-warning px-2 py-0.5 rounded-pill font-10 fw-bold">STEP 3 ACTION</span>
                                <h6 class="mb-0 fw-bold text-dark font-13 mt-0.5">Deposit $15 USDT</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Main Two-Column Activation Console --}}
        <div class="row g-4 mb-4">
            <!-- Left Column: Web3 Payment Portal -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <!-- Card Header -->
                    <div class="card-header bg-primary bg-gradient text-white p-3 p-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-primary px-2.5 py-1 rounded-pill fw-bold font-11">SLOT 1 ACTIVATION</span>
                                <span class="badge rounded-pill px-2.5 py-1 font-11 fw-bold text-white border border-white border-opacity-25" style="background: rgba(255, 255, 255, 0.18) !important; color: #ffffff !important;">
                                    <i class="fa-brands fa-ethereum me-1 text-warning"></i> BNB Smart Chain (BEP-20)
                                </span>
                            </div>
                            <h5 class="text-white fw-bold mb-0 mt-2">Peer-to-Peer On-Chain Transfer</h5>
                        </div>
                        <div class="text-md-end">
                            <span class="text-white-50 font-11 d-block text-uppercase fw-semibold">Required Deposit</span>
                            <h3 class="text-white fw-bold mb-0">$15.00 <span class="font-14 fw-normal text-white-50">USDT</span></h3>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        {{-- Wallet Connection Bar --}}
                        <div id="connected-wallet-pill" class="p-2.5 px-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2 {{ !$user->wallet_address ? 'd-none' : '' }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check text-success font-14"></i>
                                <span class="text-muted font-12 fw-semibold">Paying from connected SafePal:</span>
                                <span class="font-monospace fw-bold font-12 text-dark wallet-text">{{ $user->wallet_address ? maskAddress($user->wallet_address) : '' }}</span>
                            </div>
                            <button type="button" onclick="NevoraWeb3.connectWallet(true)" class="btn btn-link text-primary p-0 font-11 fw-semibold text-decoration-none">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Switch / Reconnect
                            </button>
                        </div>

                        <div id="unconnected-wallet-box" class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3 {{ $user->wallet_address ? 'd-none' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="fa-solid fa-wallet font-18"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark font-13">Connect your SafePal / Web3 Wallet</h6>
                                    <p class="text-muted font-11 mb-0">Connect in 1 click to enable automated $15 USDT instant slot activation.</p>
                                </div>
                            </div>
                            <button type="button" onclick="NevoraWeb3.connectWallet(true)" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-link font-12"></i> Connect SafePal
                            </button>
                        </div>

                        @if($assignedPayee)
                            <div class="row g-4 align-items-center">
                                <!-- Left: Beneficiary & Summary Card -->
                                <div class="col-12 col-md-5">
                                    <div class="p-3 bg-light rounded-4 border">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-12 fw-bold" style="width: 36px; height: 36px;">
                                                {{ substr($assignedPayee['payee_user']->name ?? 'C', 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="text-muted font-10 text-uppercase d-block fw-semibold">Assigned Beneficiary</span>
                                                <h6 class="mb-0 fw-bold font-13 text-dark">{{ $assignedPayee['payee_user']->name ?? 'Company Root' }}</h6>
                                            </div>
                                        </div>

                                        <div class="p-2.5 bg-white rounded-3 border mb-2">
                                            <div class="d-flex justify-content-between font-11 text-muted mb-1">
                                                <span>Member ID:</span>
                                                <span class="fw-bold text-dark">{{ $assignedPayee['payee_user']->member_id ?? 'COMPANY01' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between font-11 text-muted mb-1">
                                                <span>Payment Type:</span>
                                                <span class="badge {{ $assignedPayee['category'] === 'Slot Income' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} font-10 fw-bold">
                                                    {{ $assignedPayee['category'] }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between font-11 text-muted">
                                                <span>Network:</span>
                                                <span class="fw-bold text-dark"><i class="fa-brands fa-ethereum text-warning me-1"></i> BNB Smart Chain (BEP-20)</span>
                                            </div>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button class="btn btn-link btn-sm text-muted font-11 text-decoration-none" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#manual-qr-collapse" 
                                                    aria-expanded="false">
                                                <i class="fa-solid fa-qrcode me-1"></i> Show Manual QR & Address
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: 1-Click Automated Payment Action -->
                                <div class="col-12 col-md-7">
                                    <div class="p-4 rounded-4 text-center border shadow-sm" style="background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);">
                                        <div class="mb-3">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-11 fw-bold text-uppercase">
                                                Automated 1-Click Activation
                                            </span>
                                            <h4 class="fw-bold text-dark mt-2 mb-1">Pay ${{ number_format($assignedPayee['amount'], 2) }} USDT</h4>
                                            <p class="text-muted font-12 mb-0">Direct peer-to-peer on-chain transfer to assigned beneficiary.</p>
                                        </div>

                                        <!-- Primary Web3 1-Click Pay Button -->
                                        <button type="button" 
                                                id="btn-web3-pay"
                                                onclick="NevoraWeb3.paySlotWithSafePal({{ $currentSlot->slot_number ?? 1 }})"
                                                class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-lg d-flex align-items-center justify-content-center gap-2 mb-2 transition-all"
                                                style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none;">
                                            <i class="fa-solid fa-bolt font-18 text-warning"></i>
                                            <span class="font-16">Pay ${{ number_format($assignedPayee['amount'], 2) }} USDT with SafePal</span>
                                        </button>

                                        <small class="text-muted font-11 d-block mt-2">
                                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Authorize securely via your SafePal App with fingerprint / PIN.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Collapsible Alternative: Manual QR Code & Tx Hash Pasting -->
                            <div class="collapse mt-4" id="manual-qr-collapse">
                                <div class="p-3 bg-light rounded-4 border">
                                    <h6 class="fw-bold font-12 text-muted text-uppercase mb-3"><i class="fa-solid fa-sliders me-1"></i> Alternative: Manual Transfer & Hash Verification</h6>
                                    <div class="row g-3 align-items-center">
                                        <div class="col-12 col-md-4 text-center">
                                            <div class="p-2 bg-white rounded-3 border d-inline-block shadow-sm">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $assignedPayee['wallet_address'] }}" 
                                                     alt="Payee QR Code" 
                                                     class="img-fluid rounded-2" 
                                                     style="width: 110px; height: 110px;" />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <label class="form-label font-11 fw-bold text-uppercase text-muted mb-1">Payee BEP-20 Wallet Address (USDT)</label>
                                            <div class="input-group mb-2 shadow-sm rounded-3 overflow-hidden">
                                                <input type="text" class="form-control font-monospace font-11 bg-white border-end-0 text-dark" id="payee-address-input" value="{{ $assignedPayee['wallet_address'] }}" readonly>
                                                <button class="btn btn-outline-secondary px-3 font-11 fw-bold" type="button" onclick="copyText('payee-address-input')">
                                                    <i class="fa-solid fa-copy me-1"></i> Copy
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('member.track.payment') }}">
                                                @csrf
                                                <input type="hidden" name="slot_number" value="{{ $currentSlot->slot_number ?? 1 }}">
                                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                                    <input type="text" name="tx_hash" class="form-control font-monospace font-11 text-dark" placeholder="Paste 0x... TxHash manually">
                                                    <button type="submit" class="btn btn-success px-3 font-11 fw-bold">
                                                        <i class="fa-solid fa-check me-1"></i> Verify Hash
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fa-solid fa-spinner fa-spin font-24 mb-2 text-primary"></i>
                                <h6 class="fw-bold mb-1">Resolving Payment Route...</h6>
                                <p class="font-12 mb-0">Please wait while the system assigns your upline beneficiary.</p>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light p-3 px-md-4 border-top text-center text-md-start d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <small class="text-muted font-11">
                            <i class="fa-solid fa-shield-halved text-primary me-1"></i> Funds are transferred 100% Peer-to-Peer directly into the payee's decentralized wallet.
                        </small>
                        <span class="badge bg-success-subtle text-success font-10 fw-bold">
                            <i class="fa-solid fa-arrows-rotate fa-spin me-1"></i> Auto-Scanner Active
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Motivation & Unlocked Perks -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-3.5">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-gift text-warning font-16"></i>
                            <span>Member Activation Benefits</span>
                        </h6>
                    </div>

                    <div class="card-body p-3.5">
                        <p class="text-muted font-12 mb-3">
                            Activating Slot 1 unlocks your full earning potential in the 3-Matrix ecosystem:
                        </p>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="rounded-circle bg-success-subtle text-success p-1.5 d-flex align-items-center justify-content-center mt-0.5" style="width: 22px; height: 22px; min-width: 22px;">
                                    <i class="fa-solid fa-check font-10"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold font-12 text-dark">Interactive 3-Matrix Placement</h6>
                                    <small class="text-muted font-11">Placed directly into your upline's dynamic matrix.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-2.5">
                                <div class="rounded-circle bg-success-subtle text-success p-1.5 d-flex align-items-center justify-content-center mt-0.5" style="width: 22px; height: 22px; min-width: 22px;">
                                    <i class="fa-solid fa-check font-10"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold font-12 text-dark">Sponsor & Direct Referral Income</h6>
                                    <small class="text-muted font-11">Earn 100% direct sponsor payments instantly.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-2.5">
                                <div class="rounded-circle bg-success-subtle text-success p-1.5 d-flex align-items-center justify-content-center mt-0.5" style="width: 22px; height: 22px; min-width: 22px;">
                                    <i class="fa-solid fa-check font-10"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold font-12 text-dark">12-Slot Simultaneous Progression</h6>
                                    <small class="text-muted font-11">Earn from all 12 slots concurrently as you advance.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-2.5">
                                <div class="rounded-circle bg-success-subtle text-success p-1.5 d-flex align-items-center justify-content-center mt-0.5" style="width: 22px; height: 22px; min-width: 22px;">
                                    <i class="fa-solid fa-check font-10"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold font-12 text-dark">Personal Referral Link & QR</h6>
                                    <small class="text-muted font-11">Unlock your invite link to build and lead your team.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Locked Link Preview -->
                        <div class="p-3 rounded-4 bg-light border text-center">
                            <i class="fa-solid fa-lock text-muted font-20 mb-1"></i>
                            <h6 class="fw-bold font-12 text-dark mb-1">Referral Link Locked</h6>
                            <p class="text-muted font-11 mb-0">Activate Slot 1 to unlock and share your unique invite link with downlines.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- ========================================================
             ACTIVE / PAID MEMBER DASHBOARD
             ======================================================== --}}
             
        {{-- Row 1: KPI Stat Cards --}}
        <div class="row g-3 mb-4">
            <!-- 1. Total On-Chain Earnings -->
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.6px;">Total Earnings</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ formatPrice($earnings['total_earnings']) }}</h4>
                            <small class="text-success fw-semibold" style="font-size: 10.5px;"><i class="fa-solid fa-arrow-trend-up me-1"></i> On-Chain</small>
                        </div>
                        <div class="rounded-3 bg-success-subtle text-success p-3 border border-success border-opacity-10">
                            <i class="fa-solid fa-chart-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Slot Matrix Incomes -->
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.6px;">Slot Matrix Income</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ formatPrice($earnings['slot_income']) }}</h4>
                            <small class="text-primary fw-semibold" style="font-size: 10.5px;">Active Slots</small>
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary p-3 border border-primary border-opacity-10">
                            <i class="fa-solid fa-sitemap fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Recycle Incomes -->
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.6px;">Recycle Incomes</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ formatPrice($earnings['recycle_income']) }}</h4>
                            <small class="text-info fw-semibold" style="font-size: 10.5px;">Side-by-side Pools</small>
                        </div>
                        <div class="rounded-3 bg-info-subtle text-info p-3 border border-info border-opacity-10">
                            <i class="fa-solid fa-arrows-rotate fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Escrow Reserved For Next Slot -->
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.6px;">Reserved For Next Slot</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ formatPrice($earnings['reserved_amount']) }}</h4>
                            <small class="text-warning fw-semibold" style="font-size: 10.5px;">Escrow Fund</small>
                        </div>
                        <div class="rounded-3 bg-warning-subtle text-warning p-3 border border-warning border-opacity-10">
                            <i class="fa-solid fa-lock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Active Slot Tier Info & Referral Link --}}
        <div class="row g-4 mb-4">
            <!-- Left: Current Active Slot Tier Status -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold font-11">ACTIVE TIER</span>
                                <h4 class="fw-bold text-dark mb-0 mt-2">
                                    {{ $currentSlot->name }} (${{ number_format($currentSlot->entry_fee, 2) }})
                                </h4>
                            </div>
                            <a href="{{ route('member.network.tree') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none fw-semibold">
                                <i class="fa-solid fa-sitemap me-1"></i> View Tree
                            </a>
                        </div>

                        <!-- Progress Bar of Received Slot Incomes -->
                        @php
                            $receivedCount = $currentPosition ? $currentPosition->received_payments_count : 0;
                            $pct = min(100, round(($receivedCount / 13) * 100));
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between text-muted font-11 fw-semibold mb-1">
                                <span>Slot Incomes Received:</span>
                                <span>{{ $receivedCount }} / 13 Payments</span>
                            </div>
                            <div class="progress" style="height: 9px; border-radius: 6px;">
                                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: {{ $pct }}%">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <span class="text-muted font-11 d-block">Direct Incomes</span>
                                    <h5 class="fw-bold text-dark mb-0 mt-1">
                                        {{ formatPrice($user->unifiedTransactions()->where('slot_number', $currentSlot->slot_number)->where('category', 'Slot Income')->sum('amount')) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <span class="text-muted font-11 d-block">Reserved for Slot {{ $currentSlot->slot_number < 12 ? $currentSlot->slot_number + 1 : 12 }}</span>
                                    <h5 class="fw-bold text-danger mb-0 mt-1">
                                        {{ formatPrice($user->unifiedTransactions()->where('slot_number', $currentSlot->slot_number)->where('category', 'Reserved for Next Slot')->sum('amount')) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <span class="text-muted font-11 d-block">Recycle Status</span>
                                    <h5 class="fw-bold text-info mb-0 mt-1">
                                        {{ $user->recyclePositions()->where('slot_id', $currentSlot->id)->count() }} Cycles
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Referral Link Card -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-share-nodes text-primary font-18"></i>
                                <h5 class="fw-bold text-dark mb-0">Referral Link</h5>
                            </div>
                            <p class="text-muted font-12 mb-3">Share your referral link to expand your 3-Matrix team.</p>

                            @php $refLink = route('member.register.form', ['sponsor' => $user->member_id]); @endphp
                            <div class="mb-3">
                                <label class="form-label font-11 text-muted fw-semibold">Your Unique Link</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-12 bg-light border-end-0" id="ref-link-input" value="{{ $refLink }}" readonly>
                                    <button class="btn btn-primary px-3" type="button" onclick="copyText('ref-link-input')">
                                        <i class="fa-solid fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="row g-2 text-center pt-2">
                                <div class="col-4">
                                    <div class="p-2 border-end">
                                        <span class="text-muted font-10 d-block">Direct Sponsors</span>
                                        <h6 class="fw-bold text-dark mb-0 mt-1">{{ $directsCount }}</h6>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border-end">
                                        <span class="text-muted font-10 d-block">Active Directs</span>
                                        <h6 class="fw-bold text-success mb-0 mt-1">{{ $activeDirectsCount }}</h6>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2">
                                        <span class="text-muted font-10 d-block">Total Downline</span>
                                        <h6 class="fw-bold text-primary mb-0 mt-1">{{ $totalDownlineCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-top mt-3 d-flex justify-content-between">
                            <a href="{{ route('member.network.directs') }}" class="btn btn-link p-0 font-12 text-decoration-none fw-semibold text-primary">
                                View Direct Team <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                            <a href="{{ route('member.network.business') }}" class="btn btn-link p-0 font-12 text-decoration-none fw-semibold text-dark">
                                Team Turnover <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: 12-Slot Progression Matrix --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-cubes-stacked text-primary"></i>
                        <span>12-Slot Progression Matrix</span>
                    </h5>
                    <p class="text-muted font-12 mb-0">Track your simultaneous earnings and autopool activations across all 12 slots.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 font-11 fw-bold">
                    All Slots Earn Concurrently
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row row-cols-2 row-cols-md-4 row-cols-xl-6 g-3">
                    @foreach($allSlots as $s)
                        @php
                            $pos = $userPositions->get($s->slot_number);
                            $isActive = $pos !== null;
                            $isCurrent = $user->current_slot_id == $s->slot_number;
                            $completed = $pos && $pos->is_completed;
                        @endphp
                        <div class="col">
                            <div class="card border {{ $isCurrent ? 'border-primary shadow-sm bg-primary-subtle bg-opacity-10' : ($isActive ? 'border-success-subtle bg-light bg-opacity-50' : 'border-light-subtle bg-light bg-opacity-25') }} rounded-3 text-center h-100 p-2.5 position-relative overflow-hidden transition-all">
                                @if($isCurrent)
                                    <div class="position-absolute top-0 end-0 bg-primary text-white px-2 py-0.5 rounded-bottom-start small" style="font-size: 9px; font-weight: 700;">CURRENT</div>
                                @endif
                                <div class="my-1">
                                    <span class="badge {{ $isActive ? ($completed ? 'bg-info' : 'bg-success') : 'bg-secondary' }} rounded-pill font-9 px-2">
                                        Slot {{ $s->slot_number }}
                                    </span>
                                </div>
                                <h6 class="fw-bold font-13 mb-0 mt-1 {{ $isActive ? 'text-primary' : 'text-muted' }}">
                                    ${{ number_format($s->entry_fee, 0) }}
                                </h6>
                                <span class="text-muted font-10 d-block text-truncate mt-0.5">{{ $s->name }}</span>
                                <div class="mt-2 pt-1 border-top border-light-subtle font-10">
                                    @if($isActive)
                                        <span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> {{ $pos->received_payments_count }}/13</span>
                                    @else
                                        <span class="text-muted"><i class="fa-solid fa-lock"></i> Locked</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Row 4: Recent On-Chain Incomes Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-receipt text-primary me-2"></i> Recent On-Chain Incomes</h5>
                        <a href="{{ route('member.wallet.transactions') }}" class="btn btn-link btn-sm text-decoration-none text-primary fw-semibold">
                            View Full Ledger <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Slot</th>
                                        <th>Amount</th>
                                        <th>Tx Hash</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentIncomes as $tx)
                                        <tr>
                                            <td class="small">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <span class="badge {{ $tx->category === 'Reserved for Next Slot' ? 'bg-warning-subtle text-warning' : ($tx->category === 'Recycle Income' ? 'bg-info-subtle text-info' : 'bg-success-subtle text-success') }} px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                    {{ $tx->category }}
                                                </span>
                                            </td>
                                            <td class="small fw-semibold">Slot {{ $tx->slot_number ?? '1' }}</td>
                                            <td class="fw-bold text-success">+{{ formatPrice($tx->amount) }}</td>
                                            <td>
                                                @if($tx->tx_hash)
                                                    <a href="https://bscscan.com/tx/{{ $tx->tx_hash }}" target="_blank" class="font-monospace small text-primary text-decoration-none">
                                                        {{ maskAddress($tx->tx_hash) }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">Internal</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 10px;">Completed</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted small">
                                                No verified transactions recorded yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Official Community & Platform Support Card --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="card-body p-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; min-width: 50px; background: linear-gradient(135deg, rgba(29, 78, 216, 0.12), rgba(2, 132, 199, 0.22));">
                            <i class="fa-solid fa-headset text-primary font-20"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Official Community & Support</h5>
                            <p class="text-muted small mb-0">Need assistance or want to connect with global members? Reach out through our official channels.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        @if(getSetting('company_email') || getSetting('support_email'))
                            <a href="mailto:{{ getSetting('company_email') ?? getSetting('support_email') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-envelope"></i>
                                <span>{{ getSetting('company_email') ?? getSetting('support_email') }}</span>
                            </a>
                        @endif

                        @if(getSetting('company_phone') || getSetting('whatsapp_number'))
                            @php $phoneNum = preg_replace('/[^0-9]/', '', getSetting('whatsapp_number') ?? getSetting('company_phone')); @endphp
                            <a href="https://wa.me/{{ $phoneNum }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp"></i>
                                <span>WhatsApp Support</span>
                            </a>
                        @endif

                        @if(getSetting('telegram_link'))
                            <a href="{{ getSetting('telegram_link') }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="fa-brands fa-telegram"></i>
                                <span>Telegram Community</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Announcements Popup Modal (Placed at body root to prevent backdrop stacking entrapment) --}}
    @if($announcements->count() > 0)
        <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" style="z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.2);">
                                <i class="fa-solid fa-bullhorn text-white font-14"></i>
                            </div>
                            <h6 class="modal-title fw-bold text-white mb-0" id="announcementModalLabel">
                                {{ $announcements->first()->title }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted mb-0" style="line-height: 1.6; font-size: 13.5px;">
                            {{ $announcements->first()->content }}
                        </p>
                    </div>
                    <div class="modal-footer border-0 p-3 px-4 pt-0">
                        <button type="button" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm" data-bs-dismiss="modal">
                            <i class="fa-solid fa-check me-1"></i> Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endpush

@push('scripts')
<script>
    function copyText(id) {
        const input = document.getElementById(id);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);
        if (typeof toastrMessage === 'function') {
            toastrMessage('Address copied to clipboard!', 'success');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: input.value,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            alert('Copied to clipboard: ' + input.value);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const announcementModal = document.getElementById('announcementModal');
        if (announcementModal && !sessionStorage.getItem('announcement_seen')) {
            const modal = new bootstrap.Modal(announcementModal);
            modal.show();
            sessionStorage.setItem('announcement_seen', 'true');
        }
    });
</script>
@endpush
