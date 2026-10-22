<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all ">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="javascript:;" class="navbar-brand" id="sidebar-logo-area">

            <!--Logo start-->
            @if (getSetting('full_logo'))
                <div class="logo-main">
                    <div class="logo-mini">
                        <img src="{{ asset('assets/images/logos.png') }}"
                            alt="{{ getSetting('software_name') ?? config('app.name') }}">
                    </div>
                </div>
                <!--logo End-->
                <h4 class="logo-title">
                    <img src="{{ getSetting('full_logo') }}" alt="{{ getSetting('software_name') ?? config('app.name') }}"
                        style="width: 100% !important;">
                </h4>
            @else
                <div class="logo-main">
                    <div class="logo-normal">
                        <img src="{{ asset('assets/images/logos.png') }}"
                            alt="{{ getSetting('software_name') ?? config('app.name') }}">
                    </div>
                    <div class="logo-mini">
                        <img src="{{ asset('assets/images/logos.png') }}"
                            alt="{{ getSetting('software_name') ?? config('app.name') }}">
                    </div>
                </div>
                <!--logo End-->
                <h4 class="logo-title">
                    <img src="{{ asset('assets/images/logo.png') }}"
                        alt="{{ getSetting('software_name') ?? config('app.name') }}">
                </h4>
            @endif
        </a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" height="20" viewBox="-2 -2 26 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list">
            <!-- Sidebar Menu Start -->
            <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Home</span>
                        {{-- <span class="mini-icon">HO</span> --}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.dashboard') ? 'active' : '' }}" aria-current="page"
                        href="{{ route('member.dashboard') }}">
                        <i class="fa-brands fa-dashcube icon"></i>
                        <span class="item-name">Dashboard</span>
                    </a>
                </li>
                @php
                    $directCount = 0;
                    if (auth()->check() && !isAdmin()) {
                        $sponsorPos = \App\Models\UserMainTreePosition::where('user_id', authUser()->id)->first();
                        $directCount = $sponsorPos ? count($sponsorPos->child_ids ?? []) : 0;
                    }
                @endphp
                @if (isAdmin() || authUser()->is_paid)
                    <li class="nav-item">
                        @if($directCount >= 5 && !isAdmin())
                            <a class="nav-link" aria-current="page" href="{{ route('member.dashboard') }}" onclick="alert('You already have 5 directs. Please use the Referral Link Generator on your dashboard to select a placement member for new downlines.');">
                                <i class="fa-solid fa-user-plus icon"></i>
                                <span class="item-name">Join Now</span>
                            </a>
                        @else
                            <a class="nav-link" aria-current="page"
                                href="{{ route('member.register.form', ['sponsor' => authUser()->member_id]) }}">
                                <i class="fa-solid fa-user-plus icon"></i>
                                <span class="item-name">Join Now</span>
                            </a>
                        @endif
                    </li>
                @endif
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Geneology</span>
                        {{-- <span class="mini-icon">GE</span> --}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.network') ? 'active' : '' }}" aria-current="page"
                        href="{{ route('member.network') }}">
                        <i class="fa-solid fa-sitemap icon"></i>
                        <span class="item-name">Network</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.my-referrals') ? 'active' : '' }}"
                        aria-current="page" href="{{ route('member.my-referrals') }}">
                        <i class="fa-solid fa-network-wired icon"></i>
                        <span class="item-name">My Directs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.team-hierarchy') ? 'active' : '' }}"
                        aria-current="page" href="{{ route('member.team-hierarchy') }}">
                        <i class="fa-solid fa-arrow-down-wide-short icon"></i>
                        <span class="item-name">Team Hierarchy</span>
                    </a>
                </li>
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Pool & Wallet</span>
                        {{-- <span class="mini-icon">WA</span> --}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.topup.*') ? 'active' : '' }}"
                        href="{{ route('member.topup.index') }}">
                        <i class="fa-solid fa-layer-group icon"></i>
                        <span class="item-name">Package Upgrade</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.withdrawals.*') ? 'active' : '' }}"
                        href="{{ route('member.withdrawals.index') }}">
                        <i class="fa-solid fa-hand-holding-dollar icon"></i>
                        <span class="item-name">Withdrawals</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.wallet.transactions') ? 'active' : '' }}"
                        href="{{ route('member.wallet.transactions') }}">
                        <i class="fa-solid fa-receipt icon"></i>
                        <span class="item-name">Transactions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('member.kyc.*') ? 'active' : '' }}"
                        href="{{ route('member.kyc.index') }}">
                        <i class="fa-solid fa-id-card icon"></i>
                        <span class="item-name">KYC Verification</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>