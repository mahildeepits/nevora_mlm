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
            @endphp
            <div class="card border-0 shadow-sm rounded-4 mb-3 border-start border-4 border-{{ $alertType }}">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $alertType }}-subtle text-{{ $alertType }} px-3 py-1 rounded-pill font-12 fw-bold text-uppercase">
                                <i class="fa-solid {{ $icon }} me-1"></i> {{ $item->type }}
                            </span>
                            <h5 class="fw-bold mb-0 text-dark">{{ $item->title }}</h5>
                        </div>
                        <div class="text-muted font-12">
                            <i class="fa-regular fa-clock me-1"></i> {{ $item->created_at->format('d M, Y h:i A') }}
                        </div>
                    </div>
                    <hr class="my-2 opacity-25">
                    <div class="text-secondary font-14 lh-base mt-3">
                        {!! nl2br(e($item->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center text-muted">
                    <i class="fa-solid fa-bullhorn font-36 text-muted mb-2 opacity-50 d-block"></i>
                    <h6 class="fw-bold text-muted">No Announcements Published</h6>
                    <p class="font-12 mb-0">System updates and news from the administration will be displayed here.</p>
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
