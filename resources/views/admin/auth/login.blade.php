@extends('layouts.auth')

@section('content')
<style>
    .admin-auth-card {
        border-radius: 24px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .custom-input-group {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        width: 100%;
    }
    .custom-input-group:focus-within {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
    }
    .custom-input-group.has-error {
        border-color: #dc3545 !important;
    }
    .custom-input-group .input-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 16px;
        font-size: 15px;
        color: #64748b;
    }
    .custom-input-group .form-control {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 13px 14px;
        font-size: 14px;
        color: #0f172a;
        border-radius: 0 !important;
        flex-grow: 1;
    }
    .custom-input-group .form-control:focus {
        box-shadow: none !important;
        outline: none !important;
    }
</style>

<div class="row m-0 align-items-center vh-100 justify-content-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="col-md-7 col-lg-5 col-xl-4">
        <div class="card admin-auth-card border-0 p-3 bg-white">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/logo-main.png') }}" alt="NEVORA" class="img-fluid mb-2" style="max-height: 80px; object-fit: contain;">
                    <h4 class="fw-bold text-dark mb-1">Admin Portal</h4>
                    <p class="text-muted small">Sign in to manage NEVORA Platform</p>
                </div>

                <x-alerts />

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold font-12 text-muted text-uppercase mb-1">Admin Email Address</label>
                        <div class="custom-input-group @error('email') has-error @enderror">
                            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@nevora.com') }}" placeholder="admin@nevora.com" required autofocus>
                        </div>
                        @error('email')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold font-12 text-muted text-uppercase mb-1">Admin Password</label>
                        <div class="custom-input-group @error('password') has-error @enderror">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        @error('password')<div class="invalid-feedback d-block font-11 mt-1">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In to Admin Panel
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <a href="{{ route('member.login.form') }}" class="text-muted small text-decoration-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Member Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
