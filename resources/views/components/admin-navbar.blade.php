<nav class="nav navbar navbar-expand-xl navbar-light p-0">

    <div class="container-fluid navbar-inner d-flex justify-content-between align-items-center">
        <button data-trigger="navbar_main" class="d-xl-none btn p-2" type="button">
            <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z">
                </path>
            </svg>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand me-0 d-flex align-items-center gap-2">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" style="height: 36px; width: 36px; object-fit: contain;">
            <img src="{{ asset('assets/images/logo-text.png') }}" alt="NEVORA" style="height: 22px; object-fit: contain;">
        </a>

        {{-- Real Time Clock --}}
        <div class="live-clock-container mx-auto d-none d-lg-flex gap-2 ps-3 py-1 align-items-center"
            style="justify-items: center;">
            <i class="fa fa-calendar text-primary fw-bold" style="font-size: 12px; line-height: 1.2;"></i>
            <div id="live-date" class="fw-bold text-dark" style="font-size: 11px; line-height: 1.2;"></div>
            <i class="fa fa-clock text-primary fw-bold" style="font-size: 12px; line-height: 1.2;"></i>
            <div id="live-time" class="text-primary fw-bold" style="font-size: 11px; line-height: 1.2;"></div>
        </div>

        <div class="d-flex align-items-center">
            <ul class="navbar-nav navbar-list me-3">
                <li class="nav-item dropdown">
                    <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('assets/images/userblue.png') }}" alt="User-Profile"
                            class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded rounded-pill">
                        <div class="caption ms-3 d-none d-md-block ">
                            <h6 class="mb-0 caption-title">{{ authUser('admin')?->name ?? 'Admin' }}</h6>
                            <p class="mb-0 caption-sub-title text-muted fw-normal" style="font-size: 10px !important;">
                                {{ authUser('admin')?->role?->name ?? 'Admin' }}
                            </p>
                        </div>
                    </a>
                    <ul class="dropdown-menu shadow dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('admin.slots.index') }}">Slot Manager</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}">Logout</a></li>
                    </ul>
                </li>
            </ul>

            {{-- No toggler/collapse here as the menu is in the blue bar below --}}
        </div>
    </div>




    {{-- <div class="container-fluid navbar-inner">
        <button data-trigger="navbar_main" class="d-xl-none btn btn-primary rounded-pill p-1 pt-0" type="button">
            <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z">
                </path>
            </svg>
        </button>
        <a href="/" class="navbar-brand">
            <img src="{{ asset('assets/images/logof.png') }}"
                alt="{{ getSetting('software_name') ?? config('app.name') }}" height="30">
            <h4 class="logo-title text-black ms-2">
                <img src="{{ asset('assets/images/logo.png') }}"
                    alt="{{ getSetting('software_name') ?? config('app.name') }}" height="20">
            </h4>
        </a>
        <div class="input-group search-input">
            <span class="input-group-text" id="search-input">
                <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></circle>
                    <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
            <input type="search" class="form-control" placeholder="Search...">
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
                <span class="navbar-toggler-bar bar1 mt-2"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
            </span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto  navbar-list mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a href="#" class="search-toggle nav-link" id="dropdownMenuButton2" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <img src="{{ asset('template/hope-ui/images/Flag/flag001.png') }}"
                            class="img-fluid rounded-circle" alt="user"
                            style="height: 30px; min-width: 30px; width: 30px;">
                        <span class="bg-primary"></span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown">
                        <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M19.7695 11.6453C19.039 10.7923 18.7071 10.0531 18.7071 8.79716V8.37013C18.7071 6.73354 18.3304 5.67907 17.5115 4.62459C16.2493 2.98699 14.1244 2 12.0442 2H11.9558C9.91935 2 7.86106 2.94167 6.577 4.5128C5.71333 5.58842 5.29293 6.68822 5.29293 8.37013V8.79716C5.29293 10.0531 4.98284 10.7923 4.23049 11.6453C3.67691 12.2738 3.5 13.0815 3.5 13.9557C3.5 14.8309 3.78723 15.6598 4.36367 16.3336C5.11602 17.1413 6.17846 17.6569 7.26375 17.7466C8.83505 17.9258 10.4063 17.9933 12.0005 17.9933C13.5937 17.9933 15.165 17.8805 16.7372 17.7466C17.8215 17.6569 18.884 17.1413 19.6363 16.3336C20.2118 15.6598 20.5 14.8309 20.5 13.9557C20.5 13.0815 20.3231 12.2738 19.7695 11.6453Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M14.0088 19.2283C13.5088 19.1215 10.4627 19.1215 9.96275 19.2283C9.53539 19.327 9.07324 19.5566 9.07324 20.0602C9.09809 20.5406 9.37935 20.9646 9.76895 21.2335L9.76795 21.2345C10.2718 21.6273 10.8632 21.877 11.4824 21.9667C11.8123 22.012 12.1482 22.01 12.4901 21.9667C13.1083 21.877 13.6997 21.6273 14.2036 21.2345L14.2026 21.2335C14.5922 20.9646 14.8734 20.5406 14.8983 20.0602C14.8983 19.5566 14.4361 19.327 14.0088 19.2283Z"
                                fill="currentColor"></path>
                        </svg>
                        <span class="bg-danger dots"></span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('assets/images/userblue.png') }}" alt="User-Profile"
                            class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
                        <div class="caption ms-3 d-none d-md-block ">
                            <h6 class="mb-0 caption-title">Admin User</h6>
                            <p class="mb-0 caption-sub-title">System Administrator</p>
                        </div>
                    </a>
                    <ul class="dropdown-menu shadow dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('admin.logout') }}">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div> --}}
</nav>