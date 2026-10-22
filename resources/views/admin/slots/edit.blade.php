@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary">Edit Slot {{ $slot->slot_number }} ({{ $slot->name }})</h4>
                <p class="text-muted small mb-0">Modify fee structures, deduction logic, and recycle parameters.</p>
            </div>
            <a href="{{ route('admin.slots.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Slots
            </a>
        </div>

        <x-alerts />

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.slots.update', $slot) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Slot Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $slot->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Entry Fee ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="entry_fee" class="form-control @error('entry_fee') is-invalid @enderror" value="{{ old('entry_fee', $slot->entry_fee) }}" required>
                            </div>
                            @error('entry_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Reserved Unit Amount ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="reserved_unit_amount" class="form-control @error('reserved_unit_amount') is-invalid @enderror" value="{{ old('reserved_unit_amount', $slot->reserved_unit_amount) }}" required>
                            </div>
                            <small class="text-muted font-11">Unit reserved during payments 3, 4, 5 (Total = Unit x 3).</small>
                            @error('reserved_unit_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Company Deduction ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="company_deduction" class="form-control @error('company_deduction') is-invalid @enderror" value="{{ old('company_deduction', $slot->company_deduction) }}" required>
                            </div>
                            <small class="text-muted font-11">Total platform fee deducted during progression.</small>
                            @error('company_deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Net Payout Unit ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="net_payout_amount" class="form-control @error('net_payout_amount') is-invalid @enderror" value="{{ old('net_payout_amount', $slot->net_payout_amount) }}" required>
                            </div>
                            <small class="text-muted font-11">Net amount credited to user per incoming payment.</small>
                            @error('net_payout_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Max Recycles Allowed <span class="text-danger">*</span></label>
                            <input type="number" name="max_recycles" class="form-control @error('max_recycles') is-invalid @enderror" value="{{ old('max_recycles', $slot->max_recycles) }}" min="0" max="50" required>
                            <small class="text-muted font-11">Number of recycle pool cycles (e.g. 11 down to 0).</small>
                            @error('max_recycles')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Slot Active Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ old('is_active', $slot->is_active) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !old('is_active', $slot->is_active) ? 'selected' : '' }}>Inactive / Paused</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
