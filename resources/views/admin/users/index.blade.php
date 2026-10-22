@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    {{-- Filters Card --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, ID, email or wallet..." value="{{ request('search') }}">
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
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-3 rounded-pill">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Users Table Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-users me-2"></i> Registered Members ({{ $users->total() }})
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Member</th>
                                <th>Contact</th>
                                <th>Sponsor / Parent</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th>BEP-20 Wallet</th>
                                <th>Joined</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $u)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $u->profile_image_url }}" alt="{{ $u->name }}" class="avatar avatar-35 rounded-circle border">
                                            <div>
                                                <div class="fw-bold font-13">{{ $u->name }}</div>
                                                <span class="badge bg-soft-primary text-primary font-10 font-monospace">{{ $u->member_id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-12">{{ $u->email }}</div>
                                        <small class="text-muted font-11">{{ $u->mobile ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="font-11">Sponsor: <strong class="text-primary font-monospace">{{ $u->sponsor_id ?? '-' }}</strong></div>
                                        <small class="text-muted font-11 font-monospace">Parent: {{ $u->parent_id ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info font-11">Slot {{ $u->current_slot_id }}</span>
                                    </td>
                                    <td>
                                        @if($u->is_paid)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-circle-check me-1"></i> Paid
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-clock me-1"></i> Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($u->wallet_address)
                                            <span class="font-monospace font-11 text-muted" title="{{ $u->wallet_address }}">
                                                {{ maskAddress($u->wallet_address) }}
                                            </span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary font-10">Not Bound</span>
                                        @endif
                                    </td>
                                    <td class="font-12 text-muted">{{ $u->created_at->format('d M, Y') }}</td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary" title="Edit Member">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="{{ route('admin.users.login_as', $u) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Login as User">
                                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                            </a>
                                            @if(!$u->is_paid)
                                                <form action="{{ route('admin.users.manual_activate', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Manually activate this member into Slot 1?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Manual Activation">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No members found matching your search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-end">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
