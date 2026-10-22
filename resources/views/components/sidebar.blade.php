<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a href="{{ route('member.dashboard') }}" class="navbar-brand" id="sidebar-logo-area">
            <div class="logo-main d-flex align-items-center gap-2">
                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" class="img-fluid" style="width: 36px; height: 36px; object-fit: contain;">
                <h4 class="logo-title fw-bold text-primary mb-0" style="letter-spacing: 1.5px; font-size: 1.3rem;">NEVORA</h4>
            </div>
        </a>
        
        <!-- Mobile Drawer Close Button -->
        <button type="button" class="btn btn-sm btn-icon btn-light rounded-circle shadow-none d-xl-none me-2" id="sidebarCloseBtn" aria-label="Close Sidebar" style="width: 32px; height: 32px; min-width: 32px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-xmark font-16 text-muted"></i>
        </button>

        <!-- Desktop Mini Toggle -->
        <div class="sidebar-toggle d-none d-xl-flex" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" height="20" viewBox="-2 -2 26 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>
    
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list">
            <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Main Menu</span>
                    </a>
                </li>
                
                {{-- 1. Dashboard --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.dashboard*') ? 'active' : '' }}" href="{{ route('member.dashboard') }}">
                        <i class="fa-solid fa-house-chimney icon"></i>
                        <span class="item-name">Dashboard</span>
                    </a>
                </li>

                {{-- Join Now / Referral Link (Active only when paid) --}}
                @if(authUser() && authUser()->is_paid)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.invite*') ? 'active' : '' }}" href="{{ route('member.invite') }}">
                        <i class="fa-solid fa-user-plus icon"></i>
                        <span class="item-name fw-bold">Invite Member</span>
                    </a>
                </li>
                @endif

                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Network & Team</span>
                    </a>
                </li>

                {{-- 2. Network (4 Modules) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.network.tree*') ? 'active' : '' }}" href="{{ route('member.network.tree') }}">
                        <i class="fa-solid fa-sitemap icon"></i>
                        <span class="item-name">3-Matrix Tree</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.network.directs*') ? 'active' : '' }}" href="{{ route('member.network.directs') }}">
                        <i class="fa-solid fa-users icon"></i>
                        <span class="item-name">My Directs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.network.hierarchy*') ? 'active' : '' }}" href="{{ route('member.network.hierarchy') }}">
                        <i class="fa-solid fa-layer-group icon"></i>
                        <span class="item-name">Team Hierarchy</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.network.business*') ? 'active' : '' }}" href="{{ route('member.network.business') }}">
                        <i class="fa-solid fa-chart-pie icon"></i>
                        <span class="item-name">Downline Business</span>
                    </a>
                </li>

                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Finance & Ledger</span>
                    </a>
                </li>

                {{-- 3. Unified Transactions Ledger --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.wallet.transactions*') ? 'active' : '' }}" href="{{ route('member.wallet.transactions') }}">
                        <i class="fa-solid fa-receipt icon"></i>
                        <span class="item-name">Transactions Ledger</span>
                    </a>
                </li>

                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Account & Info</span>
                    </a>
                </li>

                {{-- 4. Announcements & News --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.announcements*') ? 'active' : '' }}" href="{{ route('member.announcements.index') }}">
                        <i class="fa-solid fa-bullhorn icon"></i>
                        <span class="item-name">Announcements</span>
                    </a>
                </li>

                {{-- 5. Profile --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.profile*') ? 'active' : '' }}" href="{{ route('member.profile.index') }}">
                        <i class="fa-solid fa-user-gear icon"></i>
                        <span class="item-name">My Profile</span>
                    </a>
                </li>

                {{-- 6. Logout --}}
                <li class="nav-item">
                    <a class="nav-link text-danger" href="{{ route('member.logout') }}">
                        <i class="fa-solid fa-arrow-right-from-bracket icon text-danger"></i>
                        <span class="item-name">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
