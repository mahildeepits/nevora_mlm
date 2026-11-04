@extends('layouts.member')

@section('header_title', 'Platform Announcements & News')
@section('header_subtitle', 'Stay updated with official platform announcements, system updates, and network news.')

@section('content')
<div class="row">
    <div class="col-12">
        <x-alerts />
    </div>

    <div class="col-12">
        @forelse($announcements as $item)
            @php
                $alertType = match($item->type) {
                    'success' => 'success',
                    'warning' => 'warning',
                    'danger' => 'danger',
                    default => 'primary',
                };
                $icon = match($item->type) {
                    'success' => 'fa-circle-check',
                    'warning' => 'fa-triangle-exclamation',
                    'danger' => 'fa-circle-exclamation',
                    default => 'fa-bullhorn',
                };
                $borderLeftColor = match($item->type) {
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'danger' => '#ef4444',
                    default => '#3b82f6',
                };
            @endphp
            <div class="card border-0 shadow-sm rounded-4 mb-3 position-relative overflow-hidden" style="border-left: 4px solid {{ $borderLeftColor }} !important;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="badge bg-{{ $alertType }}-subtle text-{{ $alertType }} px-3 py-1.5 rounded-pill font-11 fw-bold text-uppercase d-inline-flex align-items-center gap-1.5">
                                <i class="fa-solid {{ $icon }}"></i> {{ $item->type }}
                            </span>
                            <h5 class="fw-bold mb-0 text-white">{{ $item->title }}</h5>
                        </div>
                        <div class="text-muted font-12 d-flex align-items-center gap-1">
                            <i class="fa-regular fa-clock"></i> {{ $item->created_at->format('d M, Y h:i A') }}
                        </div>
                    </div>
                    <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.08);">
                    <div class="announcement-content font-14 lh-lg" style="color: #cbd5e1 !important;">
                        {!! nl2br(e($item->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center text-muted">
                    <i class="fa-solid fa-bullhorn font-36 text-muted mb-2 opacity-50 d-block"></i>
                    <h6 class="fw-bold text-white mb-1">No Announcements Published</h6>
                    <p class="font-12 mb-0 text-muted">System updates and news from the administration will be displayed here.</p>
                </div>
            </div>
        @endforelse

        @if($announcements->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
