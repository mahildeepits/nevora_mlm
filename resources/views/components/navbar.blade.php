<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar" style="padding: 16px 16px;">
    <div class="container-fluid navbar-inner p-0 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <!-- Mobile Sidebar Drawer Trigger (Hamburger Menu, visible on mobile/tablet only) -->
            <button type="button" 
                    class="btn btn-sm btn-icon btn-primary rounded-circle shadow-sm d-xl-none me-2 mobile-sidebar-toggle-btn" 
                    id="mobileSidebarToggleBtn" 
                    aria-label="Toggle Sidebar Menu"
                    style="width: 36px; height: 36px; min-width: 36px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-bars font-14"></i>
            </button>

            <!-- Brand Logo (Visible on mobile/tablet only, hidden on desktop because desktop sidebar has it) -->
            <a href="{{ route('member.dashboard') }}" class="navbar-brand d-flex d-xl-none align-items-center gap-2 text-decoration-none p-0 me-auto">
                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" class="img-fluid" style="width: 32px; height: 32px; object-fit: contain;">
                <h4 class="logo-title text-primary fw-bold mb-0" style="letter-spacing: 1px; font-size: 1.2rem;">NEVORA</h4>
            </a>
        </div>

        <div class="d-flex align-items-center">
            <ul class="navbar-nav ms-auto align-items-center navbar-list mb-0 flex-row">
                {{-- Theme Switcher (Dark / Light) --}}
                <li class="nav-item me-2 me-md-3">
                    <a href="javascript:void(0);" onclick="toggleTheme()" class="btn btn-sm btn-icon btn-outline-secondary rounded-pill shadow-none" id="theme-btn" title="Toggle Light/Dark Theme">
                        <i class="fa-solid fa-moon font-16 theme-icon"></i>
                    </a>
                </li>

                @php $user = authUser(); @endphp
                @if($user)
                    {{-- Wallet Address Pill (Desktop/Tablet) --}}
                    <li class="nav-item me-3 d-none d-lg-block">
                        @if($user->wallet_address)
                            <div class="navbar-wallet-pill px-3 py-1.5 rounded-pill d-flex align-items-center gap-2">
                                <i class="fa-solid fa-wallet text-primary font-13"></i>
                                <span class="font-monospace fw-bold font-12">
                                    {{ maskAddress($user->wallet_address) }}
                                </span>
                            </div>
                        @else
                            <a href="{{ route('member.profile.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 font-11 fw-bold d-flex align-items-center gap-1.5 shadow-none text-decoration-none">
                                <i class="fa-solid fa-wallet"></i> Link Wallet
                            </a>
                        @endif
                    </li>

                    {{-- Active Status Badge (Desktop/Tablet) --}}
                    <li class="nav-item me-3 d-none d-md-block">
                        @if($user->is_paid)
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 11px;">
                                <i class="fa-solid fa-circle-check me-1"></i> Slot {{ $user->current_slot_id }} Active
                            </span>
                        @else
                            <a href="{{ route('member.dashboard') }}" class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold text-decoration-none" style="font-size: 11px;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Activate Slot 1
                            </a>
                        @endif
                    </li>

                    {{-- User Profile Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ $user->profile_image_url }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-40 rounded-circle border border-2 border-primary">
                            <div class="caption ms-2 d-none d-md-block text-start">
                                <h6 class="mb-0 caption-title fw-bold" style="font-size: 13px;">{{ $user->name }}</h6>
                                <p class="mb-0 caption-sub-title text-muted" style="font-size: 11px;">{{ $user->member_id }}</p>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown" style="border-radius: 14px;">
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('member.profile.index') }}">
                                    <i class="fa-solid fa-user me-2 text-primary"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('member.wallet.transactions') }}">
                                    <i class="fa-solid fa-receipt me-2 text-info"></i> Transactions Ledger
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('member.announcements.index') }}">
                                    <i class="fa-solid fa-bullhorn me-2 text-warning"></i> Announcements
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('member.logout') }}">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
