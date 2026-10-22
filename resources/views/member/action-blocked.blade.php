@extends('layouts.member')

@section('header_title', 'Action Under Process')
@section('header_subtitle', 'This feature is currently undergoing maintenance and will be available soon.')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5 position-relative overflow-hidden">
            {{-- Decorative Background Glow --}}
            <div class="position-absolute top-0 start-50 translate-middle-x" 
                 style="width: 320px; height: 180px; background: radial-gradient(circle, rgba(58, 87, 232, 0.12) 0%, rgba(58, 87, 232, 0) 70%); pointer-events: none;">
            </div>

            <div class="card-body p-0 position-relative">
                {{-- Animated Icon Container --}}
                <div class="mb-4 d-inline-block">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" 
                         style="width: 90px; height: 90px; background: linear-gradient(135deg, rgba(58, 87, 232, 0.12), rgba(99, 102, 241, 0.22)); border: 2px solid rgba(58, 87, 232, 0.25);">
                        <i class="fa-solid fa-clock-rotate-left text-primary" style="font-size: 40px;"></i>
                    </div>
                </div>

                {{-- Status Pill --}}
                <div class="mb-3">
                    <span class="badge bg-warning-subtle text-warning px-3 py-1.5 rounded-pill fw-bold font-12">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Action Temporarily Paused
                    </span>
                </div>

                {{-- Main Headings --}}
                <h3 class="fw-bold text-dark mb-2" style="letter-spacing: -0.5px;">Feature Under Process</h3>
                <p class="lead fw-bold text-primary mb-3" style="font-size: 1.25rem;">
                    {{ $message ?? 'This action is under process, It will be available soon.' }}
                </p>

                {{-- Informational Text --}}
                <p class="text-muted font-14 mx-auto mb-4" style="max-width: 500px; line-height: 1.6;">
                    Our development engine is actively optimizing on-chain synchronization and account security.
                    Your account registration, Member ID, and team network structure remain completely intact and secure.
                </p>

                {{-- Action Navigation Buttons --}}
                <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pt-2 mb-4">
                    <a href="{{ route('member.dashboard') }}" class="btn btn-primary px-4 py-2.5 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-house"></i>
                        <span>Return to Dashboard</span>
                    </a>
                    <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('member.dashboard') }}'" 
                            class="btn btn-outline-secondary px-4 py-2.5 rounded-pill fw-bold shadow-none d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Go Back</span>
                    </button>
                </div>

                {{-- Quick Nav Shortcuts to Allowed Menus --}}
                <div class="border-top pt-4 mt-2">
                    <small class="text-muted font-11 text-uppercase fw-bold d-block mb-3">Available Menus for Browsing</small>
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                        <a href="{{ route('member.network.tree') }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none font-12 fw-semibold">
                            <i class="fa-solid fa-sitemap text-primary me-1"></i> Referral Tree
                        </a>
                        <a href="{{ route('member.network.directs') }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none font-12 fw-semibold">
                            <i class="fa-solid fa-user-group text-primary me-1"></i> Direct Partners
                        </a>
                        <a href="{{ route('member.wallet.transactions') }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none font-12 fw-semibold">
                            <i class="fa-solid fa-receipt text-primary me-1"></i> Transactions
                        </a>
                        <a href="{{ route('member.profile.index') }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none font-12 fw-semibold">
                            <i class="fa-solid fa-user text-primary me-1"></i> My Profile
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
