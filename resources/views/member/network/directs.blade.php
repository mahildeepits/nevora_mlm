@extends('layouts.member')

@section('header_title', 'My Direct Referrals')
@section('header_subtitle', 'List of all members personally sponsored with your unique referral link.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Filters & Search Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <form action="{{ route('member.network.directs') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, member ID or email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Active (Paid)</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Pending (Unpaid)</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 rounded-pill">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('member.network.directs') }}" class="btn btn-outline-secondary px-3 rounded-pill">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Directs Table Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-users me-2"></i> Direct Members ({{ $directs->total() }})
                </h5>
                @if(authUser()->is_paid)
                    <a href="{{ route('member.register.form', ['sponsor' => authUser()->member_id]) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-user-plus me-1"></i> Invite New Member
                    </a>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Member</th>
                                <th>Contact Details</th>
                                <th>Joined Date</th>
                                <th>Current Slot</th>
                                <th>Status</th>
                                <th>BEP-20 Wallet</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($directs as $index => $direct)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $directs->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $direct->profile_image_url }}" alt="{{ $direct->name }}" class="avatar avatar-35 rounded-circle border">
                                            <div>
                                                <div class="fw-bold font-13">{{ $direct->name }}</div>
                                                <span class="badge bg-soft-primary text-primary font-10 font-monospace">{{ $direct->member_id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-12">{{ $direct->email }}</div>
                                        <small class="text-muted font-11">{{ $direct->mobile ?? 'No phone' }}</small>
                                    </td>
                                    <td class="font-12 text-muted">{{ $direct->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge bg-soft-info text-info font-11">Slot {{ $direct->current_slot_id }}</span>
                                    </td>
                                    <td>
                                        @if($direct->is_paid)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-circle-check me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-clock me-1"></i> Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($direct->wallet_address)
                                            <span class="font-monospace font-11 text-muted" title="{{ $direct->wallet_address }}">
                                                {{ maskAddress($direct->wallet_address) }}
                                            </span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary font-10">Not Bound</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('member.network.tree', ['memberId' => $direct->member_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 font-11" title="View in Matrix Tree">
                                            <i class="fa-solid fa-sitemap me-1"></i> Tree
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users-slash font-36 text-muted mb-2 opacity-50 d-block"></i>
                                        No direct sponsored members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($directs->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-end">
                    {{ $directs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
