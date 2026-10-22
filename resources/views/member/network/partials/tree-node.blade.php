@if($node)
    <a href="{{ route('member.network.tree', ['memberId' => $node['member_id']]) }}" 
       class="tree-node-card {{ $node['is_paid'] ? 'active-node' : 'unpaid-node' }} text-dark d-inline-block text-decoration-none">
        
        <div class="position-relative d-inline-block">
            <img src="{{ $node['image'] }}" alt="{{ $node['name'] }}" class="tree-avatar">
            <span class="position-absolute bottom-0 end-0 badge rounded-pill {{ $node['is_paid'] ? 'bg-success' : 'bg-warning text-dark' }} p-1" style="font-size: 8px;">
                <i class="fa-solid {{ $node['is_paid'] ? 'fa-check' : 'fa-clock' }}"></i>
            </span>
        </div>

        <div class="fw-bold font-12 text-truncate" title="{{ $node['name'] }}">{{ Str::limit($node['name'], 12) }}</div>
        <div class="badge bg-soft-primary text-primary font-10 px-2 py-0 mb-1 font-monospace">{{ $node['member_id'] }}</div>

        <div class="d-flex justify-content-center align-items-center gap-1 font-10 text-muted">
            <span>Slot {{ $node['current_slot_id'] }}</span>
            <span>&bull;</span>
            <span><i class="fa-solid fa-users font-9"></i> {{ $node['directs_count'] }}</span>
        </div>
    </a>
@else
    <div class="tree-node-card vacant-node text-center py-3">
        <div class="avatar avatar-40 rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-2 text-muted border">
            <i class="fa-solid fa-user-plus font-14"></i>
        </div>
        <div class="fw-bold font-11 text-muted">Vacant</div>
        <div class="font-10 text-muted">Open Position</div>
    </div>
@endif
