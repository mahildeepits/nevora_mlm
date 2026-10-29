@extends('layouts.member')

@section('header_title', '3-Matrix Interactive Tree')
@section('header_subtitle', 'Visual 3-matrix tree view of your network organization down to Level 2 (1 + 3 + 9 = 13 nodes).')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    {{-- Tree Search & Navigation Bar --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <form action="{{ route('member.network.tree') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search downline Member ID (e.g. NV100001)" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary px-3">Search</button>
                        </div>
                    </form>

                    <div class="d-flex align-items-center gap-2">
                        @if($targetUser->id !== $loggedInUser->id)
                            @if($parentUser)
                                <a href="{{ route('member.network.tree', ['memberId' => $parentUser->member_id]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-arrow-up me-1"></i> Up One Level
                                </a>
                            @endif
                            <a href="{{ route('member.network.tree') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-house-user me-1"></i> Back to My Root
                            </a>
                        @endif

                        {{-- Legend Badges --}}
                        <div class="d-none d-md-flex align-items-center gap-2 ps-3 border-start">
                            <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                            <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-clock me-1"></i> Unpaid</span>
                            <span class="badge bg-light text-muted border px-2 py-1"><i class="fa-solid fa-circle-plus me-1"></i> Vacant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tree View Canvas Container --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-sitemap me-2"></i>
                    Viewing Hierarchy: <span class="text-dark">{{ $targetUser->name }} ({{ $targetUser->member_id }})</span>
                </h5>
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill font-12">
                    Current Slot: Slot {{ $targetUser->current_slot_id }}
                </span>
            </div>

            <div class="card-body p-4 text-center overflow-auto nevora-tree-canvas" style="min-height: 600px;">
                
                {{-- Tree Hierarchy Container --}}
                <div class="nevora-tree-wrapper py-4" style="min-width: 900px;">
                    
                    {{-- LEVEL 0: ROOT NODE --}}
                    <div class="tree-level level-0 mb-5 position-relative d-inline-block">
                        @include('member.network.partials.tree-node', ['node' => $treeData, 'isRoot' => true])
                    </div>

                    {{-- LEVEL 1: 3 CHILDREN --}}
                    <div class="tree-level level-1 mb-5 position-relative d-flex justify-content-around">
                        @foreach($treeData['children'] as $index => $childNode)
                            <div class="tree-branch text-center" style="width: 32%;">
                                @include('member.network.partials.tree-node', ['node' => $childNode, 'position' => $index + 1, 'isRoot' => false])

                                {{-- LEVEL 2: 3 GRANDCHILDREN under each child --}}
                                @if($childNode && !empty($childNode['children']))
                                    <div class="tree-sub-branches d-flex justify-content-between mt-5 pt-3 position-relative">
                                        @foreach($childNode['children'] as $gcIndex => $gcNode)
                                            <div class="tree-gc-node" style="width: 31%;">
                                                @include('member.network.partials.tree-node', ['node' => $gcNode, 'position' => $gcIndex + 1, 'isRoot' => false, 'isGrandchild' => true])
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Clean Tree Connectors Styling */
.nevora-tree-wrapper {
    position: relative;
    user-select: none;
}
.nevora-tree-canvas {
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
}
[data-bs-theme="dark"] .nevora-tree-canvas {
    background: linear-gradient(180deg, #0b1120 0%, #151d30 100%) !important;
}
.tree-node-card {
    display: inline-block;
    width: 140px;
    padding: 12px 8px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border: 2px solid #e9ecef;
    transition: all 0.25s ease;
    cursor: pointer;
    text-decoration: none;
}
.tree-node-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2);
    border-color: #2563eb !important;
}
.tree-node-card.active-node {
    border-color: #16a34a;
    background: #ffffff;
}
.tree-node-card.unpaid-node {
    border-color: #f59e0b;
    background: #ffffff;
}
.tree-node-card.vacant-node {
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    opacity: 0.8;
}
[data-bs-theme="dark"] .tree-node-card {
    background: #1e293b !important;
    border-color: #334155;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
}
[data-bs-theme="dark"] .tree-node-card .text-dark {
    color: #f8fafc !important;
}
[data-bs-theme="dark"] .tree-node-card.active-node {
    border-color: #22c55e !important;
    background: #1e293b !important;
}
[data-bs-theme="dark"] .tree-node-card.unpaid-node {
    border-color: #f59e0b !important;
    background: #1e293b !important;
}
[data-bs-theme="dark"] .tree-node-card.vacant-node {
    border: 2px dashed #475569 !important;
    background: rgba(255, 255, 255, 0.03) !important;
    color: #94a3b8 !important;
}
.tree-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 6px;
    border: 2px solid #e9ecef;
}
.tree-node-card.active-node .tree-avatar {
    border-color: #16a34a;
}
.tree-node-card.unpaid-node .tree-avatar {
    border-color: #f59e0b;
}
</style>
@endsection
