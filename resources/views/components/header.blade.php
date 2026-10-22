<div class="iq-navbar-header position-relative overflow-hidden">
    <div class="container-fluid iq-container position-relative" style="z-index: 2; padding: 30px 24px 85px 24px;">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="header-main-title fw-bold mb-1" style="letter-spacing: -0.5px; font-size: 1.75rem;">
                            @yield('header_title', 'Dashboard')
                        </h2>
                        <p class="header-subtitle mb-0" style="font-size: 13.5px;">
                            @yield('header_subtitle', 'NEVORA 3-Matrix Hybrid Platform')
                        </p>
                    </div>
                    <div>
                        @yield('header_right')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="iq-header-img position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;">
        <img src="{{ asset('assets/images/header3.jpg') }}" alt="header" class="w-100 h-100 object-cover" style="opacity: 0.35; filter: saturate(1.2);">
    </div>
</div>

<style>
/* Robust, High-Contrast Header Banner */
.iq-navbar-header {
    background: linear-gradient(135deg, #0a1128 0%, #172554 45%, #0284c7 100%) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
[data-bs-theme="dark"] .iq-navbar-header {
    background: linear-gradient(135deg, #0b1120 0%, #1e293b 60%, #111827 100%) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.header-main-title {
    color: #ffffff !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
}
.header-subtitle {
    color: rgba(255, 255, 255, 0.88) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
</style>
