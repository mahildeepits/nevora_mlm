@extends('layouts.member')

@section('header_title', 'Unified Transactions Ledger')
@section('header_subtitle', 'Transparent on-chain accounting of all direct payouts, reserved funds, and matrix earnings.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Earnings Summary Cards --}}
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-wallet font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Earned</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ formatPrice($summary['total_earnings']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-cubes font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Slot Incomes</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ formatPrice($summary['slot_income']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-arrows-rotate font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Recycle Incomes</h6>
                    <h3 class="fw-bold mb-0 text-info">{{ formatPrice($summary['recycle_income']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-vault font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Reserved In Escrow</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ formatPrice($summary['reserved_amount']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <form action="{{ route('member.wallet.transactions') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search by TX hash or description..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="Slot Income" {{ request('category') === 'Slot Income' ? 'selected' : '' }}>Slot Income</option>
                            <option value="Reserved for Next Slot" {{ request('category') === 'Reserved for Next Slot' ? 'selected' : '' }}>Reserved for Next Slot</option>
                            <option value="Recycle Income" {{ request('category') === 'Recycle Income' ? 'selected' : '' }}>Recycle Income</option>
                            <option value="Activation Payment" {{ request('category') === 'Activation Payment' ? 'selected' : '' }}>Activation Payment</option>
                            <option value="Company Deduction" {{ request('category') === 'Company Deduction' ? 'selected' : '' }}>Company Deduction</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="slot" class="form-select">
                            <option value="">All Slots</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('slot') == $i ? 'selected' : '' }}>Slot {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 rounded-pill flex-grow-1">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('member.wallet.transactions') }}" class="btn btn-outline-secondary px-3 rounded-pill">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Transactions Table Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-receipt me-2"></i> Transaction Records ({{ $transactions->total() }})
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Category / Type</th>
                                <th>Slot</th>
                                <th>Amount</th>
                                <th>Party Details</th>
                                <th>BscScan Hash</th>
                                <th>Date & Time</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $tx)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $transactions->firstItem() + $index }}</td>
                                    <td>
                                        @if($tx->category === 'Slot Income')
                                            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-arrow-down-left me-1"></i> Slot Income
                                            </span>
                                        @elseif($tx->category === 'Reserved for Next Slot')
                                            <span class="badge bg-warning-subtle text-warning px-3 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-vault me-1"></i> Reserved (Slot {{ $tx->slot_number + 1 }})
                                            </span>
                                        @elseif($tx->category === 'Recycle Income')
                                            <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-arrows-rotate me-1"></i> Recycle Income
                                            </span>
                                        @elseif($tx->category === 'Company Deduction')
                                            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-building-columns me-1"></i> Deduction
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill font-11">
                                                {{ $tx->category }}
                                            </span>
                                        @endif
                                        <div class="small text-muted font-11 mt-1">{{ $tx->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary font-11">Slot {{ $tx->slot_number }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-14 {{ in_array($tx->category, ['Slot Income', 'Recycle Income']) ? 'text-success' : 'text-dark' }}">
                                            {{ in_array($tx->category, ['Slot Income', 'Recycle Income']) ? '+' : '' }}{{ formatPrice($tx->amount) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($tx->from_user_id)
                                            <div class="font-12">From: <span class="fw-bold text-primary">{{ $tx->fromUser?->member_id ?? 'User #' . $tx->from_user_id }}</span></div>
                                        @endif
                                        <small class="text-muted font-11 font-monospace">
                                            {{ $tx->to_address ? maskAddress($tx->to_address) : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($tx->tx_hash)
                                            <a href="https://bscscan.com/tx/{{ $tx->tx_hash }}" target="_blank" class="badge bg-light text-primary border font-monospace font-11 text-decoration-none">
                                                {{ maskAddress($tx->tx_hash) }} <i class="fa-solid fa-arrow-up-right-from-square font-9 ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted font-11">-</span>
                                        @endif
                                    </td>
                                    <td class="font-12 text-muted">{{ $tx->created_at->format('d M, Y h:i A') }}</td>
                                    <td class="pe-4 text-end">
                                        @if($tx->status === 'Completed')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-circle-check me-1"></i> Completed
                                            </span>
                                        @elseif($tx->status === 'Pending')
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-clock me-1"></i> Pending
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill font-11">
                                                {{ $tx->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-receipt font-36 text-muted mb-2 opacity-50 d-block"></i>
                                        No transaction records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($transactions->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-end">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
