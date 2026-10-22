@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-bullhorn me-2"></i> Announcements & News Noticeboard
                </h5>
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> New Announcement
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Preview</th>
                                <th>Status</th>
                                <th>Date Published</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $index => $item)
                                <tr>
                                    <td class="ps-4 text-muted font-12">{{ $announcements->firstItem() + $index }}</td>
                                    <td><strong class="font-13">{{ $item->title }}</strong></td>
                                    <td>
                                        <span class="badge bg-soft-{{ $item->type === 'info' ? 'primary' : $item->type }} text-{{ $item->type === 'info' ? 'primary' : $item->type }} px-2 py-1 rounded-pill font-11 text-uppercase fw-bold">
                                            {{ $item->type }}
                                        </span>
                                    </td>
                                    <td class="text-muted font-12" style="max-width: 320px;">
                                        {{ Str::limit($item->content, 80) }}
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill font-11">Published</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill font-11">Draft</span>
                                        @endif
                                    </td>
                                    <td class="font-12 text-muted">{{ $item->created_at->format('d M, Y') }}</td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.announcements.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('admin.announcements.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this announcement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No announcements published yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($announcements->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-end">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
