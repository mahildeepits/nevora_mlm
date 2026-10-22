@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary">Edit Member: {{ $user->name }} ({{ $user->member_id }})</h4>
                <p class="text-muted small mb-0">Update contact, wallet address, or account status.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Members
            </a>
        </div>

        <x-alerts />

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Mobile / Phone</label>
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile) }}">
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Current Slot</label>
                            <input type="text" class="form-control bg-light" value="Slot {{ $user->current_slot_id }}" readonly disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold font-13">BEP-20 Wallet Address (BSC)</label>
                            <input type="text" name="wallet_address" class="form-control font-monospace @error('wallet_address') is-invalid @enderror" value="{{ old('wallet_address', $user->wallet_address) }}" placeholder="0x...">
                            @error('wallet_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Paid Status</label>
                            <select name="is_paid" class="form-select">
                                <option value="1" {{ old('is_paid', $user->is_paid) ? 'selected' : '' }}>Paid / Active</option>
                                <option value="0" {{ !old('is_paid', $user->is_paid) ? 'selected' : '' }}>Unpaid / Pending</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Update Member
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
