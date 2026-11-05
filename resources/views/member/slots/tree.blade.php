@extends('layouts.member')

@section('title', 'Slot ' . $slot_number . ' Tree View - NEVORA')
@section('header_title', 'Slot ' . $slot_number . ' Tree View')
@section('header_subtitle', $targetUser->id !== $loggedInUser->id ? 'Viewing 3-matrix team for member ' . $targetUser->member_id : 'B-TITAN 3-matrix tree view with 3 legs and 13-payment lineage.')

@section('header_right')
    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <a href="{{ route('member.slots.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Slots Catalog
        </a>
        <div id="btitan-header-actions" class="{{ $targetUser->id !== $loggedInUser->id ? 'd-flex' : 'd-none' }} align-items-center gap-2">
            @if($parentUser)
                <button type="button" onclick="exploreMember('{{ $parentUser->member_id }}')" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-up me-1"></i> Up One Level
                </button>
            @endif
            <button type="button" onclick="exploreMember('')" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" style="background: linear-gradient(135deg, #1d4ed8 0%, #00d2ff 100%); border: none;">
                <i class="fa-solid fa-house-user me-1"></i> Back to My Root
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="row g-4 mb-4 justify-content-center">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Main B-TITAN Style 3-Matrix Slot Container --}}
    <div class="col-12 col-md-11 col-xl-9 col-xxl-8">
        <div class="card border-0 rounded-4 overflow-hidden shadow-lg" style="background: #0b1120; border: 1px solid rgba(0, 210, 255, 0.25) !important;">
            <div class="card-body p-3 p-sm-4">
                
                {{-- 1. B-TITAN Style Slot Title Bar --}}
                <div class="btitan-slot-banner mx-auto mb-4 text-center">
                    <h3 class="fw-bold text-white mb-0 font-22 font-sm-28">Slot {{ $slot_number }}</h3>
                </div>

                {{-- 2. Sub-Bar: Cost & Cycle Indicator --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-1 px-sm-3">
                    <!-- Left: Entry Fee with Coin Icon -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 0 12px rgba(245, 158, 11, 0.4);">
                            <i class="fa-solid fa-coins text-white font-16"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold font-16 font-sm-20" style="color: #00d2ff; text-shadow: 0 0 10px rgba(0, 210, 255, 0.4); line-height: 1.1;">
                                ${{ number_format($slot->entry_fee, 2) }}
                            </span>
                            <small class="text-white text-opacity-75 font-11 font-sm-12 fw-bold">USDT</small>
                        </div>
                    </div>

                    <!-- Right: Cycle Indicator -->
                    <div id="btitan-cycle-text" class="btitan-cycle-badge">
                        Cycle {{ $currentCycle }} of {{ $totalCycles }}
                    </div>
                </div>

                {{-- 3. Interactive Breadcrumbs Lineage Trail --}}
                <div class="btitan-breadcrumb-bar d-flex align-items-center gap-1 mb-3 px-3 py-2 flex-wrap">
                    <span class="text-white text-opacity-50 font-11 fw-semibold me-1">
                        <i class="fa-solid fa-sitemap text-cyan me-1"></i> Lineage:
                    </span>
                    <div id="btitan-breadcrumb-trail" class="d-flex align-items-center gap-1 flex-wrap">
                        @foreach($breadcrumbs as $index => $crumb)
                            @if($index > 0)
                                <i class="fa-solid fa-chevron-right font-9 text-white text-opacity-40 mx-1"></i>
                            @endif
                            @if($crumb['is_current'])
                                <span class="btitan-crumb-btn active">
                                    @if($crumb['is_root'])
                                        <i class="fa-solid fa-crown text-warning font-10 me-1"></i>
                                    @else
                                        <i class="fa-solid fa-user text-cyan font-10 me-1"></i>
                                    @endif
                                    {{ $crumb['member_id'] }}
                                </span>
                            @else
                                <button type="button" class="btitan-crumb-btn" onclick="exploreMember('{{ $crumb['is_root'] ? '' : $crumb['member_id'] }}')">
                                    @if($crumb['is_root'])
                                        <i class="fa-solid fa-house-user font-10 me-1"></i>
                                    @else
                                        <i class="fa-solid fa-user font-10 me-1"></i>
                                    @endif
                                    {{ $crumb['member_id'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Notice: Viewing Downline Member Team --}}
                <div id="btitan-viewing-notice" class="{{ $targetUser->id !== $loggedInUser->id ? 'd-flex' : 'd-none' }} alert alert-info py-2 px-3 rounded-3 mb-3 align-items-center justify-content-between gap-2" style="background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(56, 189, 248, 0.3);">
                    <span class="font-12 text-white">
                        <i class="fa-solid fa-sitemap text-cyan me-1"></i>
                        Viewing 3-matrix team for: <strong id="btitan-notice-member-id">{{ $targetUser->member_id }}</strong>
                    </span>
                    <button type="button" class="btn btn-outline-light btn-sm py-0 px-2 font-11 rounded-pill" onclick="exploreMember('')">
                        Return to Me
                    </button>
                </div>

                {{-- 4. Visual 3-Matrix Tree Canvas (3 Legs under Root, 3 Legs under each Tier-1 Node) --}}
                <div class="btitan-tree-wrapper position-relative mx-auto" style="max-width: 520px;">
                    
                    {{-- Responsive SVG Diagram (viewBox 0 0 420 280) --}}
                    <svg viewBox="0 0 420 280" class="w-100 h-auto btitan-tree-svg" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <!-- Linear Gradients for Geometric Shapes — NEVORA Brand Theme -->
                            <linearGradient id="rootGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e40af" />
                                <stop offset="100%" stop-color="#0284c7" />
                            </linearGradient>

                            <linearGradient id="recycleGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#065f46" />
                                <stop offset="100%" stop-color="#059669" />
                            </linearGradient>

                            <linearGradient id="uplineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#e11d48" />
                                <stop offset="100%" stop-color="#881337" />
                            </linearGradient>

                            <linearGradient id="incomeGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#1d4ed8" />
                                <stop offset="100%" stop-color="#0f172a" />
                            </linearGradient>

                            <linearGradient id="upgradeGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#2563eb" />
                                <stop offset="100%" stop-color="#172554" />
                            </linearGradient>

                            <linearGradient id="downlineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#f59e0b" />
                                <stop offset="100%" stop-color="#78350f" />
                            </linearGradient>
                        </defs>

                        {{-- Connecting Branch Lines for 3-Matrix (3 Legs Per Parent) --}}
                        <g stroke="#1e3a5f" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Root down to Tier 1 (3 branches) -->
                            <path d="M 210 52 L 210 84" />
                            <path d="M 75 84 L 345 84" />
                            <path d="M 75 84 L 75 116" />
                            <path d="M 210 84 L 210 116" />
                            <path d="M 345 84 L 345 116" />

                            <!-- Tier 1 Spot 1 down to 3 children (Spots 4, 5, 6) -->
                            <path d="M 75 162 L 75 190" />
                            <path d="M 30 190 L 120 190" />
                            <path d="M 30 190 L 30 218" />
                            <path d="M 75 190 L 75 218" />
                            <path d="M 120 190 L 120 218" />

                            <!-- Tier 1 Spot 2 down to 3 children (Spots 7, 8, 9) -->
                            <path d="M 210 162 L 210 190" />
                            <path d="M 165 190 L 255 190" />
                            <path d="M 165 190 L 165 218" />
                            <path d="M 210 190 L 210 218" />
                            <path d="M 255 190 L 255 218" />

                            <!-- Tier 1 Spot 3 down to 3 children (Spots 10, 11, 12) -->
                            <path d="M 345 162 L 345 190" />
                            <path d="M 300 190 L 390 190" />
                            <path d="M 300 190 L 300 218" />
                            <path d="M 345 190 L 345 218" />
                            <path d="M 390 190 L 390 218" />
                        </g>

                        {{-- NODES CONTAINER (Initial Blade Server Render & Target for In-Place Dynamic Shifting) --}}
                        <g id="btitan-tree-nodes">
                            {{-- ROOT NODE: NEVORA Brand Sapphire/Cyan Capsule (Contains ONLY Member ID) --}}
                            <g class="btitan-node-group btitan-node-root">
                                <rect x="135" y="14" width="150" height="38" rx="10" fill="url(#rootGradient)" stroke="#00d2ff" stroke-width="2" />
                                <text x="210" y="39" text-anchor="middle" font-family="'Unbounded', sans-serif" font-weight="800" font-size="15" fill="#ffffff" letter-spacing="0.5">
                                    {{ $spots['root']['member_id'] }}
                                </text>
                            </g>

                            @php
                                $tier1Xs = [1 => 75, 2 => 210, 3 => 345];
                                $tier2Xs = [
                                    4 => 30, 5 => 75, 6 => 120,
                                    7 => 165, 8 => 210, 9 => 255,
                                    10 => 300, 11 => 345, 12 => 390
                                ];
                            @endphp

                            {{-- TIER 1: 3 LEGS UNDER ROOT (Spots 1, 2, 3) --}}
                            @foreach($tier1Xs as $spotIndex => $x)
                                @php
                                    $sp = $spots[(string)$spotIndex];
                                    $isFilled = $sp['is_filled'] && $sp['member_id'] !== '-';
                                    $memId = $sp['member_id'];
                                    $shape = $sp['shape'];
                                    $roleLabel = $sp['role_label'];
                                    $fs = strlen($memId) > 6 ? '10' : '12.5';
                                @endphp

                                @if($isFilled)
                                    <g class="btitan-node-filled" onclick="exploreMember('{{ $memId }}')" style="cursor: pointer;">
                                        <title>Click to explore team of {{ $memId }} ({{ $roleLabel }})</title>
                                        @if($shape === 'upline_triangle')
                                            <polygon points="{{ $x }},116 {{ $x - 33 }},162 {{ $x + 33 }},162" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="2" />
                                            <text x="{{ $x }}" y="153" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'downline_triangle')
                                            <polygon points="{{ $x - 33 }},116 {{ $x + 33 }},116 {{ $x }},162" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="2" />
                                            <text x="{{ $x }}" y="133" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'upgrade_square')
                                            <rect x="{{ $x - 24 }}" y="118" width="48" height="44" rx="8" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="2" />
                                            <path d="M {{ $x + 12 }} 123 L {{ $x + 18 }} 123 L {{ $x + 18 }} 129 M {{ $x + 18 }} 123 L {{ $x + 11 }} 130" stroke="#00d2ff" stroke-width="1.4" fill="none" />
                                            <text x="{{ $x }}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'recycle_spiral')
                                            <circle cx="{{ $x }}" cy="139" r="22" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="2" />
                                            <text x="{{ $x }}" y="144" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @else
                                            <rect x="{{ $x - 24 }}" y="118" width="48" height="44" rx="8" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="2" />
                                            <text x="{{ $x }}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @endif
                                    </g>
                                @else
                                    <g class="btitan-node-vacant" style="pointer-events: none; cursor: default;">
                                        @if($shape === 'upline_triangle')
                                            <polygon points="{{ $x }},116 {{ $x - 33 }},162 {{ $x + 33 }},162" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.8" opacity="0.8" />
                                            <text x="{{ $x }}" y="153" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="14" fill="#ffffff">-</text>
                                        @elseif($shape === 'downline_triangle')
                                            <polygon points="{{ $x - 33 }},116 {{ $x + 33 }},116 {{ $x }},162" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.8" opacity="0.8" />
                                            <text x="{{ $x }}" y="133" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="14" fill="#ffffff">-</text>
                                        @elseif($shape === 'upgrade_square')
                                            <rect x="{{ $x - 24 }}" y="118" width="48" height="44" rx="8" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.6" opacity="0.8" />
                                            <path d="M {{ $x + 12 }} 123 L {{ $x + 18 }} 123 L {{ $x + 18 }} 129 M {{ $x + 18 }} 123 L {{ $x + 11 }} 130" stroke="#00d2ff" stroke-width="1.4" fill="none" />
                                            <text x="{{ $x }}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="13" fill="#ffffff">-</text>
                                        @elseif($shape === 'recycle_spiral')
                                            <circle cx="{{ $x }}" cy="139" r="22" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.8" opacity="0.8" />
                                            <text x="{{ $x }}" y="144" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="13" fill="#ffffff">-</text>
                                        @else
                                            <rect x="{{ $x - 24 }}" y="118" width="48" height="44" rx="8" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.6" opacity="0.8" />
                                            <text x="{{ $x }}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="13" fill="#ffffff">-</text>
                                        @endif
                                    </g>
                                @endif
                            @endforeach

                            {{-- TIER 2: 9 NODES ACROSS (Spots 4 to 12) --}}
                            @foreach($tier2Xs as $spotIndex => $x)
                                @php
                                    $sp = $spots[(string)$spotIndex];
                                    $isFilled = $sp['is_filled'] && $sp['member_id'] !== '-';
                                    $memId = $sp['member_id'];
                                    $shape = $sp['shape'];
                                    $roleLabel = $sp['role_label'];
                                    $fs = strlen($memId) > 6 ? '8.5' : '10.5';
                                @endphp

                                @if($isFilled)
                                    <g class="btitan-node-filled" onclick="exploreMember('{{ $memId }}')" style="cursor: pointer;">
                                        <title>Click to explore team of {{ $memId }} ({{ $roleLabel }})</title>
                                        @if($shape === 'upline_triangle')
                                            <polygon points="{{ $x }},218 {{ $x - 18 }},254 {{ $x + 18 }},254" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.8" />
                                            <text x="{{ $x }}" y="247" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'downline_triangle')
                                            <polygon points="{{ $x - 18 }},218 {{ $x + 18 }},218 {{ $x }},254" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.8" />
                                            <text x="{{ $x }}" y="235" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'upgrade_square')
                                            <rect x="{{ $x - 18 }}" y="218" width="36" height="36" rx="7" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.8" />
                                            <path d="M {{ $x + 10 }} 223 L {{ $x + 14 }} 223 L {{ $x + 14 }} 227 M {{ $x + 14 }} 223 L {{ $x + 9 }} 228" stroke="#00d2ff" stroke-width="1.2" fill="none" />
                                            <text x="{{ $x }}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @elseif($shape === 'recycle_spiral')
                                            <circle cx="{{ $x }}" cy="236" r="18" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.8" />
                                            <text x="{{ $x }}" y="240" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @else
                                            <rect x="{{ $x - 18 }}" y="218" width="36" height="36" rx="7" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.8" />
                                            <text x="{{ $x }}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="{{ $fs }}" fill="#ffffff">{{ $memId }}</text>
                                        @endif
                                    </g>
                                @else
                                    <g class="btitan-node-vacant" style="pointer-events: none; cursor: default;">
                                        @if($shape === 'upline_triangle')
                                            <polygon points="{{ $x }},218 {{ $x - 18 }},254 {{ $x + 18 }},254" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.5" opacity="0.8" />
                                            <text x="{{ $x }}" y="247" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>
                                        @elseif($shape === 'downline_triangle')
                                            <polygon points="{{ $x - 18 }},218 {{ $x + 18 }},218 {{ $x }},254" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.5" opacity="0.8" />
                                            <text x="{{ $x }}" y="235" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>
                                        @elseif($shape === 'upgrade_square')
                                            <rect x="{{ $x - 18 }}" y="218" width="36" height="36" rx="7" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.5" opacity="0.8" />
                                            <path d="M {{ $x + 10 }} 223 L {{ $x + 14 }} 223 L {{ $x + 14 }} 227 M {{ $x + 14 }} 223 L {{ $x + 9 }} 228" stroke="#00d2ff" stroke-width="1.2" fill="none" />
                                            <text x="{{ $x }}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>
                                        @elseif($shape === 'recycle_spiral')
                                            <circle cx="{{ $x }}" cy="236" r="18" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.5" opacity="0.8" />
                                            <text x="{{ $x }}" y="240" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>
                                        @else
                                            <rect x="{{ $x - 18 }}" y="218" width="36" height="36" rx="7" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.5" opacity="0.8" />
                                            <text x="{{ $x }}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>
                                        @endif
                                    </g>
                                @endif
                            @endforeach
                        </g>
                    </svg>

                </div>

                {{-- 5. Clean 6-Item 3-Matrix Legend Box --}}
                <div class="btitan-legend-box mx-auto my-4">
                    <div class="row g-2 text-start">
                        <!-- Row 1 -->
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-icon" style="width: 32px; height: 16px; border-radius: 4px; background: linear-gradient(135deg, #1e40af 0%, #0284c7 100%); border: 1.5px solid #00d2ff;"></span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">You (Root)</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-icon" style="width: 18px; height: 18px; border-radius: 4px; background: #1d4ed8; border: 1.5px solid #2563eb;"></span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">Your Income</span>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-icon position-relative" style="width: 18px; height: 18px; border-radius: 4px; background: #2563eb; border: 1.5px solid #3b82f6;">
                                <i class="fa-solid fa-arrow-up-right font-8 text-cyan position-absolute" style="top: 2px; right: 2px;"></i>
                            </span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">Buy Level (Reserve)</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-triangle-up"></span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">Upline</span>
                        </div>

                        <!-- Row 3 -->
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-triangle-down"></span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">Downline</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <span class="btitan-legend-recycle">
                                <i class="fa-solid fa-rotate font-9"></i>
                            </span>
                            <span class="font-12 font-sm-13 text-white text-opacity-80">Recycle (#13)</span>
                        </div>
                    </div>
                </div>

                {{-- 6. Bottom Navigation Buttons (Exact B-TITAN Style) --}}
                <div class="d-flex justify-content-between align-items-center gap-3 pt-2">
                    <a id="btitan-prev-btn" href="{{ $hasPrevCycle ? route('member.slots.tree', ['slot_number' => $slot_number, 'memberId' => $targetUser->member_id, 'cycle' => $currentCycle - 1]) : '#' }}" 
                       class="btitan-nav-btn flex-grow-1 {{ !$hasPrevCycle ? 'disabled opacity-35' : '' }}">
                        <i class="fa-solid fa-arrow-left me-1"></i> Previous Cycle
                    </a>

                    <a id="btitan-next-btn" href="{{ $hasNextCycle ? route('member.slots.tree', ['slot_number' => $slot_number, 'memberId' => $targetUser->member_id, 'cycle' => $currentCycle + 1]) : '#' }}" 
                       class="btitan-nav-btn flex-grow-1 {{ !$hasNextCycle ? 'disabled opacity-35' : '' }}">
                        Next Cycle <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const currentSlotNumber = {{ $slot_number }};
    const loggedInMemberId = "{{ $loggedInUser->member_id }}";
    let isExploring = false;

    // In-place dynamic tree exploration (NO page reload)
    async function exploreMember(memberId, cycle = 1) {
        if (isExploring) return;
        isExploring = true;

        const wrapper = document.querySelector('.btitan-tree-wrapper');
        if (wrapper) wrapper.classList.add('loading');

        try {
            const url = `/member/slots/${currentSlotNumber}${memberId ? '/' + encodeURIComponent(memberId) : ''}?cycle=${cycle}&ajax=1`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            if (data && data.success) {
                // Update browser URL without reload
                const pageUrl = `/member/slots/${currentSlotNumber}${memberId ? '/' + encodeURIComponent(memberId) : ''}${cycle > 1 ? '?cycle=' + cycle : ''}`;
                window.history.pushState({ memberId: memberId, cycle: cycle }, '', pageUrl);

                // Re-render SVG Tree Nodes
                renderBTitanTree(data.spots, data.targetUser);

                // Re-render Breadcrumbs Lineage
                renderBreadcrumbs(data.breadcrumbs);

                // Update notices and header buttons
                updateNavigationState(data);
            }
        } catch (err) {
            console.error('AJAX tree exploration error:', err);
        } finally {
            if (wrapper) wrapper.classList.remove('loading');
            isExploring = false;
        }
    }

    // Handle browser Back / Forward buttons
    window.addEventListener('popstate', (event) => {
        const memberId = event.state && event.state.memberId ? event.state.memberId : '';
        const cycle = event.state && event.state.cycle ? event.state.cycle : 1;
        exploreMember(memberId, cycle);
    });

    // Re-render breadcrumbs trail dynamically
    function renderBreadcrumbs(breadcrumbs) {
        const trail = document.getElementById('btitan-breadcrumb-trail');
        if (!trail || !Array.isArray(breadcrumbs)) return;

        let html = '';
        breadcrumbs.forEach((crumb, idx) => {
            if (idx > 0) {
                html += '<i class="fa-solid fa-chevron-right font-9 text-white text-opacity-40 mx-1"></i>';
            }
            if (crumb.is_current) {
                const icon = crumb.is_root 
                    ? '<i class="fa-solid fa-crown text-warning font-10 me-1"></i>' 
                    : '<i class="fa-solid fa-user text-cyan font-10 me-1"></i>';
                html += `<span class="btitan-crumb-btn active">${icon}${escapeHtml(crumb.member_id)}</span>`;
            } else {
                const icon = crumb.is_root 
                    ? '<i class="fa-solid fa-house-user font-10 me-1"></i>' 
                    : '<i class="fa-solid fa-user font-10 me-1"></i>';
                const clickId = crumb.is_root ? '' : escapeHtml(crumb.member_id);
                html += `<button type="button" class="btitan-crumb-btn" onclick="exploreMember('${clickId}')">${icon}${escapeHtml(crumb.member_id)}</button>`;
            }
        });
        trail.innerHTML = html;
    }

    // Update viewing notice and header buttons
    function updateNavigationState(data) {
        const isRoot = data.targetUser.is_root;
        const noticeEl = document.getElementById('btitan-viewing-notice');
        const noticeMemId = document.getElementById('btitan-notice-member-id');
        const headerActions = document.getElementById('btitan-header-actions');

        if (noticeEl && noticeMemId) {
            if (!isRoot) {
                noticeMemId.textContent = data.targetUser.member_id;
                noticeEl.classList.remove('d-none');
                noticeEl.classList.add('d-flex');
            } else {
                noticeEl.classList.add('d-none');
                noticeEl.classList.remove('d-flex');
            }
        }

        if (headerActions) {
            if (!isRoot) {
                headerActions.classList.remove('d-none');
                headerActions.classList.add('d-flex');
            } else {
                headerActions.classList.add('d-none');
                headerActions.classList.remove('d-flex');
            }
        }

        // Update cycle badge
        const cycleText = document.getElementById('btitan-cycle-text');
        if (cycleText) {
            cycleText.textContent = `Cycle ${data.currentCycle} of ${data.totalCycles}`;
        }

        // Update cycle buttons
        const prevBtn = document.getElementById('btitan-prev-btn');
        const nextBtn = document.getElementById('btitan-next-btn');

        if (prevBtn) {
            if (data.hasPrevCycle) {
                prevBtn.classList.remove('disabled', 'opacity-35');
                prevBtn.onclick = (e) => {
                    e.preventDefault();
                    exploreMember(data.targetUser.is_root ? '' : data.targetUser.member_id, data.currentCycle - 1);
                };
            } else {
                prevBtn.classList.add('disabled', 'opacity-35');
                prevBtn.onclick = (e) => e.preventDefault();
            }
        }

        if (nextBtn) {
            if (data.hasNextCycle) {
                nextBtn.classList.remove('disabled', 'opacity-35');
                nextBtn.onclick = (e) => {
                    e.preventDefault();
                    exploreMember(data.targetUser.is_root ? '' : data.targetUser.member_id, data.currentCycle + 1);
                };
            } else {
                nextBtn.classList.add('disabled', 'opacity-35');
                nextBtn.onclick = (e) => e.preventDefault();
            }
        }
    }

    // Render 3-matrix tree nodes dynamically inside SVG
    function renderBTitanTree(spots, targetUser) {
        const container = document.getElementById('btitan-tree-nodes');
        if (!container) return;

        let markup = '';

        // 1. Root Node (NEVORA Sapphire/Cyan Capsule)
        markup += `
            <g class="btitan-node-group btitan-node-root">
                <rect x="135" y="14" width="150" height="38" rx="10" fill="url(#rootGradient)" stroke="#00d2ff" stroke-width="2" />
                <text x="210" y="39" text-anchor="middle" font-family="'Unbounded', sans-serif" font-weight="800" font-size="15" fill="#ffffff" letter-spacing="0.5">
                    ${escapeHtml(spots.root.member_id)}
                </text>
            </g>
        `;

        // 2. Tier 1 Coordinates (3 Legs under Root)
        const tier1Xs = { 1: 75, 2: 210, 3: 345 };
        for (const [idx, x] of Object.entries(tier1Xs)) {
            const sp = spots[idx];
            if (!sp) continue;
            const isFilled = sp.is_filled && sp.member_id !== '-';
            const memId = sp.member_id;
            const shape = sp.shape;
            const roleLabel = sp.role_label || '';
            const fs = memId.length > 6 ? '10' : '12.5';

            if (isFilled) {
                markup += `<g class="btitan-node-filled" onclick="exploreMember('${escapeHtml(memId)}')" style="cursor: pointer;">`;
                markup += `<title>Click to explore team of ${escapeHtml(memId)} (${escapeHtml(roleLabel)})</title>`;
                if (shape === 'upline_triangle') {
                    markup += `<polygon points="${x},116 ${x - 33},162 ${x + 33},162" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="2" />`;
                    markup += `<text x="${x}" y="153" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'downline_triangle') {
                    markup += `<polygon points="${x - 33},116 ${x + 33},116 ${x},162" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="2" />`;
                    markup += `<text x="${x}" y="133" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'upgrade_square') {
                    markup += `<rect x="${x - 24}" y="118" width="48" height="44" rx="8" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="2" />`;
                    markup += `<path d="M ${x + 12} 123 L ${x + 18} 123 L ${x + 18} 129 M ${x + 18} 123 L ${x + 11} 130" stroke="#00d2ff" stroke-width="1.4" fill="none" />`;
                    markup += `<text x="${x}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'recycle_spiral') {
                    markup += `<circle cx="${x}" cy="139" r="22" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="2" />`;
                    markup += `<text x="${x}" y="144" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else {
                    markup += `<rect x="${x - 24}" y="118" width="48" height="44" rx="8" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="2" />`;
                    markup += `<text x="${x}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                }
                markup += `</g>`;
            } else {
                markup += `<g class="btitan-node-vacant" style="pointer-events: none; cursor: default;">`;
                if (shape === 'upline_triangle') {
                    markup += `<polygon points="${x},116 ${x - 33},162 ${x + 33},162" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.8" opacity="0.8" />`;
                    markup += `<text x="${x}" y="153" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="14" fill="#ffffff">-</text>`;
                } else if (shape === 'downline_triangle') {
                    markup += `<polygon points="${x - 33},116 ${x + 33},116 ${x},162" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.8" opacity="0.8" />`;
                    markup += `<text x="${x}" y="133" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="14" fill="#ffffff">-</text>`;
                } else if (shape === 'upgrade_square') {
                    markup += `<rect x="${x - 24}" y="118" width="48" height="44" rx="8" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.6" opacity="0.8" />`;
                    markup += `<path d="M ${x + 12} 123 L ${x + 18} 123 L ${x + 18} 129 M ${x + 18} 123 L ${x + 11} 130" stroke="#00d2ff" stroke-width="1.4" fill="none" />`;
                    markup += `<text x="${x}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="13" fill="#ffffff">-</text>`;
                } else if (shape === 'recycle_spiral') {
                    markup += `<circle cx="${x}" cy="139" r="22" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.8" opacity="0.8" />`;
                    markup += `<text x="${x}" y="144" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="800" font-size="13" fill="#ffffff">-</text>`;
                } else {
                    markup += `<rect x="${x - 24}" y="118" width="48" height="44" rx="8" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.6" opacity="0.8" />`;
                    markup += `<text x="${x}" y="145" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="13" fill="#ffffff">-</text>`;
                }
                markup += `</g>`;
            }
        }

        // 3. Tier 2 Coordinates (9 Nodes across)
        const tier2Xs = {
            4: 30, 5: 75, 6: 120,
            7: 165, 8: 210, 9: 255,
            10: 300, 11: 345, 12: 390
        };

        for (const [idx, x] of Object.entries(tier2Xs)) {
            const sp = spots[idx];
            if (!sp) continue;
            const isFilled = sp.is_filled && sp.member_id !== '-';
            const memId = sp.member_id;
            const shape = sp.shape;
            const roleLabel = sp.role_label || '';
            const fs = memId.length > 6 ? '8.5' : '10.5';

            if (isFilled) {
                markup += `<g class="btitan-node-filled" onclick="exploreMember('${escapeHtml(memId)}')" style="cursor: pointer;">`;
                markup += `<title>Click to explore team of ${escapeHtml(memId)} (${escapeHtml(roleLabel)})</title>`;
                if (shape === 'upline_triangle') {
                    markup += `<polygon points="${x},218 ${x - 18},254 ${x + 18},254" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.8" />`;
                    markup += `<text x="${x}" y="247" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'downline_triangle') {
                    markup += `<polygon points="${x - 18},218 ${x + 18},218 ${x},254" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.8" />`;
                    markup += `<text x="${x}" y="235" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'upgrade_square') {
                    markup += `<rect x="${x - 18}" y="218" width="36" height="36" rx="7" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.8" />`;
                    markup += `<path d="M ${x + 10} 223 L ${x + 14} 223 L ${x + 14} 227 M ${x + 14} 223 L ${x + 9} 228" stroke="#00d2ff" stroke-width="1.2" fill="none" />`;
                    markup += `<text x="${x}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else if (shape === 'recycle_spiral') {
                    markup += `<circle cx="${x}" cy="236" r="18" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.8" />`;
                    markup += `<text x="${x}" y="240" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                } else {
                    markup += `<rect x="${x - 18}" y="218" width="36" height="36" rx="7" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.8" />`;
                    markup += `<text x="${x}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="${fs}" fill="#ffffff">${escapeHtml(memId)}</text>`;
                }
                markup += `</g>`;
            } else {
                markup += `<g class="btitan-node-vacant" style="pointer-events: none; cursor: default;">`;
                if (shape === 'upline_triangle') {
                    markup += `<polygon points="${x},218 ${x - 18},254 ${x + 18},254" fill="url(#uplineGradient)" stroke="#ef4444" stroke-width="1.5" opacity="0.8" />`;
                    markup += `<text x="${x}" y="247" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>`;
                } else if (shape === 'downline_triangle') {
                    markup += `<polygon points="${x - 18},218 ${x + 18},218 ${x},254" fill="url(#downlineGradient)" stroke="#f59e0b" stroke-width="1.5" opacity="0.8" />`;
                    markup += `<text x="${x}" y="235" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>`;
                } else if (shape === 'upgrade_square') {
                    markup += `<rect x="${x - 18}" y="218" width="36" height="36" rx="7" fill="url(#upgradeGradient)" stroke="#3b82f6" stroke-width="1.5" opacity="0.8" />`;
                    markup += `<path d="M ${x + 10} 223 L ${x + 14} 223 L ${x + 14} 227 M ${x + 14} 223 L ${x + 9} 228" stroke="#00d2ff" stroke-width="1.2" fill="none" />`;
                    markup += `<text x="${x}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>`;
                } else if (shape === 'recycle_spiral') {
                    markup += `<circle cx="${x}" cy="236" r="18" fill="url(#recycleGradient)" stroke="#34d399" stroke-width="1.5" opacity="0.8" />`;
                    markup += `<text x="${x}" y="240" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>`;
                } else {
                    markup += `<rect x="${x - 18}" y="218" width="36" height="36" rx="7" fill="url(#incomeGradient)" stroke="#2563eb" stroke-width="1.5" opacity="0.8" />`;
                    markup += `<text x="${x}" y="241" text-anchor="middle" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="#ffffff">-</text>`;
                }
                markup += `</g>`;
            }
        }

        container.innerHTML = markup;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const prevBtn = document.getElementById('btitan-prev-btn');
        const nextBtn = document.getElementById('btitan-next-btn');
        if (prevBtn && !prevBtn.classList.contains('disabled')) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                exploreMember("{{ $targetUser->id !== $loggedInUser->id ? $targetUser->member_id : '' }}", {{ $currentCycle - 1 }});
            });
        }
        if (nextBtn && !nextBtn.classList.contains('disabled')) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                exploreMember("{{ $targetUser->id !== $loggedInUser->id ? $targetUser->member_id : '' }}", {{ $currentCycle + 1 }});
            });
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
