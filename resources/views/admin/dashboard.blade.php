@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    {{-- Platform Overview KPIs --}}
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-users font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Members</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalMembers) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-user-check font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Active / Paid</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($paidMembers) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-user-clock font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Unpaid Members</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($unpaidMembers) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-vault font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Platform Turnover</h6>
                    <h3 class="fw-bold mb-0 text-info">{{ formatPrice($totalTurnover) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Company Deductions Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; min-width: 56px;">
                        <i class="fa-solid fa-building-columns font-24"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Company Deduction Revenue</h5>
                        <p class="mb-0 text-muted small">Accumulated platform administration fees deducted from Slot 3 through Slot 11 upgrade cycles.</p>
                    </div>
                </div>
                <div class="text-end">
                    <span class="small text-muted d-block text-uppercase fw-bold font-11 mb-1">Total Accumulated</span>
                    <h2 class="fw-bold mb-0 text-primary">{{ formatPrice($companyDeductions) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- 12 Dynamic Slots Matrix Grid --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-cubes me-2"></i> 12 Dynamic Slots Platform Status
                </h5>
                <a href="{{ route('admin.slots.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-gear me-1"></i> Manage Slots
                </a>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($slotStats as $stat)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <div class="card border shadow-none rounded-3 text-center p-3 h-100 bg-light">
                                <span class="badge bg-primary rounded-pill font-11 mb-2 d-inline-block mx-auto px-3">
                                    Slot {{ $stat['slot']->slot_number }}
                                </span>
                                <div class="fw-bold font-13 text-dark mb-1">{{ $stat['slot']->name }}</div>
                                <div class="text-success fw-bold font-14 mb-2">{{ formatPrice($stat['slot']->entry_fee) }}</div>
                                <hr class="my-2 opacity-25">
                                <div class="font-11 text-muted">
                                    <i class="fa-solid fa-users me-1"></i> <strong class="text-dark">{{ $stat['count'] }}</strong> positions
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions Table --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-receipt me-2"></i> Recent Platform Transactions
                </h5>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    View Unified Ledger
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Category</th>
                                <th>Slot</th>
                                <th>Amount</th>
                                <th>Recipient</th>
                                <th>From Member</th>
                                <th>BscScan Hash</th>
                                <th>Date</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $index => $tx)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary font-11">{{ $tx->category }}</span>
                                    </td>
                                    <td>Slot {{ $tx->slot_number }}</td>
                                    <td><strong class="text-success">{{ formatPrice($tx->amount) }}</strong></td>
                                    <td>
                                        <span class="fw-bold font-12">{{ $tx->user?->name ?? 'User #' . $tx->user_id }}</span>
                                        <small class="text-muted d-block font-11 font-monospace">{{ $tx->user?->member_id ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($tx->fromUser)
                                            <span class="font-12">{{ $tx->fromUser->name }}</span>
                                            <small class="text-muted d-block font-11 font-monospace">{{ $tx->fromUser->member_id }}</small>
                                        @else
                                            <span class="text-muted font-11">System / Escrow</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tx->tx_hash)
                                            <a href="https://bscscan.com/tx/{{ $tx->tx_hash }}" target="_blank" class="font-monospace font-11 text-primary text-decoration-none">
                                                {{ maskAddress($tx->tx_hash) }}
                                            </a>
                                        @else
                                            <span class="text-muted font-11">-</span>
                                        @endif
                                    </td>
                                    <td class="font-12 text-muted">{{ $tx->created_at->format('d M, Y h:i A') }}</td>
                                    <td class="pe-4 text-end">
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">{{ $tx->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No transactions recorded yet.</td>
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
