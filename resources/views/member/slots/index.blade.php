@extends('layouts.member')

@section('title', 'NEVORA Slots - 12 Matrix Slots')
@section('header_title', 'Slots')
@section('header_subtitle', 'Explore all 12 matrix earning slots and track active network progression.')

@section('header_right')
    <div class="d-flex align-items-center gap-2">
        <div class="px-3 py-1.5 rounded-3 bg-cyan-glow text-cyan font-13 fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-cube"></i>
            <span>Active: Slot {{ $user->current_slot_id }}</span>
        </div>
        <div class="px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-white font-13 font-monospace fw-bold">
            ID {{ $user->member_id }}
        </div>
    </div>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Top Stats & Overview Bar --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, rgba(21, 29, 48, 0.95) 0%, rgba(30, 41, 69, 0.95) 100%); border: 1px solid var(--nv-border-glass) !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px; background: linear-gradient(135deg, #1d4ed8 0%, #00d2ff 100%); box-shadow: 0 0 20px rgba(0, 210, 255, 0.35);">
                            <i class="fa-solid fa-cubes-stacked text-white fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="fw-bold text-white mb-0">12 Matrix Slots</h4>
                                <span class="badge rounded-pill bg-cyan-glow font-11">BEP-20 / USDT</span>
                            </div>
                            <p class="text-muted font-12 mb-0 mt-1">
                                Active slots unlock interactive tree views with cycle-by-cycle payment routing.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 w-100 w-md-auto justify-content-between justify-content-md-end pt-2 pt-md-0 border-top border-md-0 border-secondary border-opacity-25">
                        <div class="text-start text-md-end">
                            <span class="text-muted font-11 text-uppercase fw-semibold d-block">Total Slots Earned</span>
                            <h3 class="fw-bold text-cyan mb-0">${{ number_format($totalSlotEarnings, 2) }} <span class="font-13 text-muted fw-normal">USDT</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 12-Slot Responsive Grid Catalog --}}
    <div class="col-12">
        <div class="nv-slots-grid">
            @foreach($slotsData as $slot)
                @if($slot['is_active'])
                    {{-- ACTIVE SLOT CARD (Clickable to Interactive Tree) --}}
                    <a href="{{ route('member.slots.tree', ['slot_number' => $slot['slot_number']]) }}" class="nv-slot-card active-slot">
                        <div>
                            <!-- Top Header -->
                            <div class="nv-slot-header">
                                <div class="nv-slot-number-badge">
                                    {{ $slot['slot_number'] }}
                                </div>
                                <div class="nv-slot-cost-badge">
                                    <span>${{ number_format($slot['entry_fee'], 2) }}</span>
                                    <span class="text-cyan font-11">USDT</span>
                                </div>
                            </div>

                            <!-- Mini Matrix Diagram Preview -->
                            <div class="nv-slot-diagram-preview">
                                <div class="nv-slot-node-indicators">
                                    <div class="nv-node-dot {{ $slot['team_count'] >= 1 ? 'filled' : '' }}">1</div>
                                    <div class="nv-node-dot {{ $slot['team_count'] >= 2 ? 'filled' : '' }}">2</div>
                                    <div class="nv-node-dot {{ $slot['team_count'] >= 3 ? 'filled' : '' }}">3</div>
                                    <div class="nv-node-dot {{ $slot['team_count'] >= 4 ? 'filled' : '' }}">4</div>
                                    <div class="nv-node-dot recycle-dot {{ $slot['recycle_count'] >= 1 ? 'filled' : '' }}" title="Recycle Node">
                                        <i class="fa-solid fa-arrows-rotate font-8"></i>
                                    </div>
                                </div>

                                <!-- 3-Metrics Row -->
                                <div class="nv-slot-metrics">
                                    <div class="nv-slot-metric-item">
                                        <span class="nv-slot-metric-label">Team</span>
                                        <span class="nv-slot-metric-value">{{ $slot['team_count'] }}</span>
                                    </div>
                                    <div class="nv-slot-metric-item">
                                        <span class="nv-slot-metric-label">Multiplier</span>
                                        <span class="nv-slot-metric-value text-cyan">{{ $slot['multiplier'] }}</span>
                                    </div>
                                    <div class="nv-slot-metric-item">
                                        <span class="nv-slot-metric-label">Cycles</span>
                                        <span class="nv-slot-metric-value text-warning">{{ $slot['recycle_count'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="nv-slot-footer">
                            <div>
                                <span class="nv-slot-earning-label d-block">Slot Earnings</span>
                                <span class="nv-slot-earning-val">${{ number_format($slot['total_earning'], 2) }}</span>
                            </div>
                            <span class="btn btn-sm btn-outline-primary rounded-pill px-3 font-11 fw-bold d-flex align-items-center gap-1 border-cyan-glow text-cyan">
                                View Tree <i class="fa-solid fa-arrow-right font-10"></i>
                            </span>
                        </div>
                    </a>
                @else
                    {{-- LOCKED SLOT CARD (Frosted Glass Blur Overlay) --}}
                    <div class="nv-slot-card locked">
                        <div>
                            <!-- Top Header -->
                            <div class="nv-slot-header">
                                <div class="nv-slot-number-badge" style="background: #334155; opacity: 0.7;">
                                    {{ $slot['slot_number'] }}
                                </div>
                                <div class="nv-slot-cost-badge text-muted">
                                    <span>${{ number_format($slot['entry_fee'], 2) }}</span>
                                    <span class="font-11 text-muted">USDT</span>
                                </div>
                            </div>

                            <!-- Mini Matrix Diagram Preview -->
                            <div class="nv-slot-diagram-preview" style="opacity: 0.35;">
                                <div class="nv-slot-node-indicators">
                                    <div class="nv-node-dot">1</div>
                                    <div class="nv-node-dot">2</div>
                                    <div class="nv-node-dot">3</div>
                                    <div class="nv-node-dot">4</div>
                                    <div class="nv-node-dot recycle-dot"><i class="fa-solid fa-arrows-rotate font-8"></i></div>
                                </div>
                                <div class="nv-slot-metrics">
                                    <div class="nv-slot-metric-item"><span class="nv-slot-metric-label">Team</span><span class="nv-slot-metric-value">0</span></div>
                                    <div class="nv-slot-metric-item"><span class="nv-slot-metric-label">Multiplier</span><span class="nv-slot-metric-value">0.0x</span></div>
                                    <div class="nv-slot-metric-item"><span class="nv-slot-metric-label">Cycles</span><span class="nv-slot-metric-value">0</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="nv-slot-footer" style="opacity: 0.4;">
                            <span class="nv-slot-earning-label">Slot Earnings</span>
                            <span class="nv-slot-earning-val text-muted">$0.00</span>
                        </div>

                        <!-- Frosted Blur Overlay -->
                        <div class="nv-slot-locked-overlay">
                            <div class="nv-slot-lock-icon">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h6 class="fw-bold text-white mb-1 font-14">Slot {{ $slot['slot_number'] }} Locked</h6>
                            <p class="text-muted font-11 mb-3">Upgrade your slot level to unlock earnings and tree hierarchy.</p>
                            <a href="{{ route('member.dashboard') }}" class="btn btn-sm btn-primary rounded-pill px-3 font-11 fw-bold" style="background: linear-gradient(135deg, #1d4ed8 0%, #00d2ff 100%); border: none;">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Activate ${{ number_format($slot['entry_fee'], 0) }}
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection

