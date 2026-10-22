@extends('layouts.member')

@section('header_title', 'Downline Business Report')
@section('header_subtitle', 'Volume turnover and performance metrics across each of your direct referral legs.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Performance KPI Cards --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-users font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Direct Legs</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ count($businessReport) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-layer-group font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Team Size</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalNetworkUsers }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-sack-dollar font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Network Volume</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ formatPrice($totalNetworkVolume) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Business by Direct Leg Table --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-chart-pie me-2"></i> Turnover Breakdown by Direct Leg
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Direct Leg Leader</th>
                                <th>Total Members</th>
                                <th>Active (Paid)</th>
                                <th>Leg Turnover ($)</th>
                                <th>Volume Share</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($businessReport as $index => $leg)
                                @php
                                    $pct = $totalNetworkVolume > 0 ? round(($leg['turnover'] / $totalNetworkVolume) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $leg['direct']->profile_image_url }}" alt="{{ $leg['direct']->name }}" class="avatar avatar-35 rounded-circle border">
                                            <div>
                                                <div class="fw-bold font-13">{{ $leg['direct']->name }}</div>
                                                <span class="badge bg-soft-primary text-primary font-10 font-monospace">{{ $leg['direct']->member_id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold font-13">{{ $leg['total_members'] }}</span>
                                        <small class="text-muted font-11">members</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">
                                            {{ $leg['paid_members'] }} Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success font-14">{{ formatPrice($leg['turnover']) }}</div>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="font-11 fw-bold text-muted">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('member.network.tree', ['memberId' => $leg['direct']->member_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 font-11">
                                            <i class="fa-solid fa-sitemap me-1"></i> View Leg
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-chart-line font-36 text-muted mb-2 opacity-50 d-block"></i>
                                        No direct sponsored legs yet.
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
@endsection
