@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-cubes me-2"></i> 12 Dynamic Slots Manager
                    </h5>
                    <small class="text-muted">Configure entry fees, reserve units, company deductions, and recycle limits across all 12 slots.</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Slot #</th>
                                <th>Name</th>
                                <th>Entry Fee</th>
                                <th>Reserve Unit (x3)</th>
                                <th>Company Deduction</th>
                                <th>Net Payout Unit</th>
                                <th>Max Recycles</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($slots as $slot)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-primary rounded-pill px-3 py-2 font-12 fw-bold">Slot {{ $slot->slot_number }}</span>
                                    </td>
                                    <td class="fw-bold font-13">{{ $slot->name }}</td>
                                    <td><strong class="text-success font-14">{{ formatPrice($slot->entry_fee) }}</strong></td>
                                    <td>
                                        <span class="text-warning fw-bold">{{ formatPrice($slot->reserved_unit_amount) }}</span>
                                        <small class="text-muted d-block font-11">(Total: {{ formatPrice($slot->reserved_unit_amount * 3) }})</small>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold">{{ formatPrice($slot->company_deduction) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">{{ formatPrice($slot->net_payout_amount) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info font-11">{{ $slot->max_recycles }} times</span>
                                    </td>
                                    <td>
                                        @if($slot->is_active)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">
                                                <i class="fa-solid fa-circle-check me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill font-11">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.slots.edit', $slot) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-12">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
