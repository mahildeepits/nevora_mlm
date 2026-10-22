@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary">Edit Announcement</h4>
                <p class="text-muted small mb-0">Modify announcement title, content or publishing status.</p>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Announcements
            </a>
        </div>

        <x-alerts />

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold font-13">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $announcement->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Notice Category / Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>Information (Blue)</option>
                                <option value="success" {{ old('type', $announcement->type) === 'success' ? 'selected' : '' }}>Success / Milestone (Green)</option>
                                <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>Notice / Alert (Yellow)</option>
                                <option value="danger" {{ old('type', $announcement->type) === 'danger' ? 'selected' : '' }}>Critical / Maintenance (Red)</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-13">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ old('is_active', $announcement->is_active) ? 'selected' : '' }}>Published</option>
                                <option value="0" {{ !old('is_active', $announcement->is_active) ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold font-13">Announcement Content <span class="text-danger">*</span></label>
                            <textarea name="content" rows="6" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Update Announcement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
