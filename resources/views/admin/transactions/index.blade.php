@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    {{-- Platform Financial Totals --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-receipt font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Platform Volume</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ formatPrice($totalVolume) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-building-columns font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Company Deductions</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ formatPrice($companyDeductions) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-vault font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Escrow Funds Reserved</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ formatPrice($reservedTotal) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.transactions.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search by TX hash, member name, ID or wallet..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="Slot Income" {{ request('category') === 'Slot Income' ? 'selected' : '' }}>Slot Income</option>
                            <option value="Reserved for Next Slot" {{ request('category') === 'Reserved for Next Slot' ? 'selected' : '' }}>Reserved for Next Slot</option>
                            <option value="Recycle Income" {{ request('category') === 'Recycle Income' ? 'selected' : '' }}>Recycle Income</option>
                            <option value="Company Deduction" {{ request('category') === 'Company Deduction' ? 'selected' : '' }}>Company Deduction</option>
                            <option value="Activation Payment" {{ request('category') === 'Activation Payment' ? 'selected' : '' }}>Activation Payment</option>
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
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary px-3 rounded-pill">
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
                    <i class="fa-solid fa-receipt me-2"></i> Unified Platform Ledger ({{ $transactions->total() }})
                </h5>
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
                                <th>Beneficiary</th>
                                <th>Sender</th>
                                <th>BscScan Hash</th>
                                <th>Timestamp</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $tx)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $transactions->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary font-11">{{ $tx->category }}</span>
                                        <div class="small text-muted font-11 mt-1">{{ $tx->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-11">Slot {{ $tx->slot_number }}</span>
                                    </td>
                                    <td>
                                        <strong class="font-14 text-dark">{{ formatPrice($tx->amount) }}</strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-12">{{ $tx->user?->name ?? 'User #' . $tx->user_id }}</div>
                                        <span class="badge bg-soft-secondary text-secondary font-10 font-monospace">{{ $tx->user?->member_id ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($tx->fromUser)
                                            <div class="font-12">{{ $tx->fromUser->name }}</div>
                                            <span class="font-10 font-monospace text-muted">{{ $tx->fromUser->member_id }}</span>
                                        @else
                                            <span class="text-muted font-11">Escrow / System</span>
                                        @endif
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
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">{{ $tx->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No transactions found matching your criteria.</td>
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
