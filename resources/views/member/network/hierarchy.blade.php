@extends('layouts.member')

@section('header_title', 'Downline Team Hierarchy')
@section('header_subtitle', 'Comprehensive list of all team members across your downline network.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Filters Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <form action="{{ route('member.network.hierarchy') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
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
                        <a href="{{ route('member.network.hierarchy') }}" class="btn btn-outline-secondary px-3 rounded-pill">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Downline Table Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-layer-group me-2"></i> Downline Network ({{ $team->total() }})
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Member</th>
                                <th>Sponsor / Parent</th>
                                <th>Current Slot</th>
                                <th>Status</th>
                                <th>BEP-20 Wallet</th>
                                <th>Joined Date</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($team as $index => $member)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $team->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $member->profile_image_url }}" alt="{{ $member->name }}" class="avatar avatar-35 rounded-circle border">
                                            <div>
                                                <div class="fw-bold font-13">{{ $member->name }}</div>
                                                <span class="badge bg-soft-primary text-primary font-10 font-monospace">{{ $member->member_id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-12"><strong>Sponsor:</strong> <span class="text-primary font-monospace">{{ $member->sponsor_id ?? '-' }}</span></div>
                                        <small class="text-muted font-11"><strong>Parent:</strong> <span class="font-monospace">{{ $member->parent_id ?? '-' }}</span></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info font-11">Slot {{ $member->current_slot_id }}</span>
                                    </td>
                                    <td>
                                        @if($member->is_paid)
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
                                        @if($member->wallet_address)
                                            <span class="font-monospace font-11 text-muted" title="{{ $member->wallet_address }}">
                                                {{ maskAddress($member->wallet_address) }}
                                            </span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary font-10">Not Bound</span>
                                        @endif
                                    </td>
                                    <td class="font-12 text-muted">{{ $member->created_at->format('d M, Y') }}</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('member.network.tree', ['memberId' => $member->member_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 font-11" title="View in Matrix Tree">
                                            <i class="fa-solid fa-sitemap me-1"></i> Tree
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users-slash font-36 text-muted mb-2 opacity-50 d-block"></i>
                                        No downline members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($team->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-end">
                    {{ $team->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
