<nav class="nav navbar navbar-expand-xl navbar-light iq-navbar navs-transparent navs-sticky">
  <div class="container-fluid navbar-inner">
    <a href="/" class="navbar-brand">

      <!--Logo start-->
      <div class="logo-main">
        <!-- <div class="logo-normal">
          <img src="{{ asset('assets/images/logos.png') }}"
            alt="{{ getSetting('software_name') ?? config('app.name') }}" height="40">
        </div> -->
        <div class="logo-mini">
          <img src="{{ asset('assets/images/logos.png') }}"
            alt="{{ getSetting('software_name') ?? config('app.name') }}" height="50">
        </div>
      </div>
      <!--logo End-->
      <h4 class="logo-title">
        <img src="{{ asset('assets/images/logo.png') }}" alt="{{ getSetting('software_name') ?? config('app.name') }}"
          height="40">
      </h4>
    </a>
    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
      <i class="icon">
        <svg width="20px" class="icon-20" viewBox="0 0 24 24">
          <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
        </svg>
      </i>
    </div>
    <div class="input-group search-input">
      <span class="input-group-text" id="search-input">
        <svg class="icon-18" width="18" viewBox="0 0 24 30" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
            stroke-linejoin="round"></circle>
          <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
            stroke-linejoin="round"></path>
        </svg>
      </span>
      <input type="search" class="form-control" placeholder="Search...">
    </div>

    {{-- Real Time Clock --}}
    <div class="live-clock-container mx-auto d-none d-lg-flex gap-3 ps-3 py-1 align-items-center"
      style="justify-items: center;">
      <div class="d-flex align-items-center gap-1">
        <i class="fa fa-calendar text-white-50" style="font-size: 13px;"></i>
        <div id="live-date" class="fw-bold text-white" style="font-size: 12px;"></div>
      </div>
      <div class="vr bg-white-50 opacity-25" style="height: 15px;"></div>
      <div class="d-flex align-items-center gap-1">
        <i class="fa fa-clock text-white-50" style="font-size: 13px;"></i>
        <div id="live-time" class="text-white fw-bold" style="font-size: 12px;"></div>
      </div>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon">
        <span class="mt-2 navbar-toggler-bar bar1"></span>
        <span class="navbar-toggler-bar bar2"></span>
        <span class="navbar-toggler-bar bar3"></span>
      </span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
        {{-- <li class="nav-item dropdown">
          <a href="#" class="search-toggle nav-link" id="dropdownMenuButton2" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <img src="{{ asset('template/hope-ui/images/Flag/flag001.png') }}" class="img-fluid rounded-circle"
              alt="user" style="height: 30px; min-width: 30px; width: 30px;">
            <span class="bg-primary"></span>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown">
            <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M19.7695 11.6453C19.039 10.7923 18.7071 10.0531 18.7071 8.79716V8.37013C18.7071 6.73354 18.3304 5.67907 17.5115 4.62459C16.2493 2.98699 14.1244 2 12.0442 2H11.9558C9.91935 2 7.86106 2.94167 6.577 4.5128C5.71333 5.58842 5.29293 6.68822 5.29293 8.37013V8.79716C5.29293 10.0531 4.98284 10.7923 4.23049 11.6453C3.67691 12.2738 3.5 13.0815 3.5 13.9557C3.5 14.8309 3.78723 15.6598 4.36367 16.3336C5.11602 17.1413 6.17846 17.6569 7.26375 17.7466C8.83505 17.9258 10.4063 17.9933 12.0005 17.9933C13.5937 17.9933 15.165 17.8805 16.7372 17.7466C17.8215 17.6569 18.884 17.1413 19.6363 16.3336C20.2118 15.6598 20.5 14.8309 20.5 13.9557C20.5 13.0815 20.3231 12.2738 19.7695 11.6453Z"
                fill="currentColor"></path>
              <path opacity="0.4"
                d="M14.0088 19.2283C13.5088 19.1215 10.4627 19.1215 9.96275 19.2283C9.53539 19.327 9.07324 19.5566 9.07324 20.0602C9.09809 20.5406 9.37935 20.9646 9.76895 21.2335L9.76795 21.2345C10.2718 21.6273 10.8632 21.877 11.4824 21.9667C11.8123 22.012 12.1482 22.01 12.4901 21.9667C13.1083 21.877 13.6997 21.6273 14.2036 21.2345L14.2026 21.2335C14.5922 20.9646 14.8734 20.5406 14.8983 20.0602C14.8983 19.5566 14.4361 19.327 14.0088 19.2283Z"
                fill="currentColor"></path>
            </svg>
            <span class="bg-danger dots"></span>
          </a>
        </li> --}}
        <!-- Change Theme -->
        {{-- <li class="nav-item me-3 d-flex align-items-center">
          <a href="javascript:void(0);" class="nav-link text-body p-0" id="theme-toggle" onclick="toggleTheme()"
            title="Toggle Light/Dark Mode">
            <span class="theme-icon-light d-none">
              <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2" />
                <path
                  d="M12 2V3M12 21V22M4 12H3M22 12H21M5.6 5.6L6.4 6.4M18.4 18.4L17.6 17.6M5.6 18.4L6.4 17.6M18.4 5.6L17.6 6.4"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" />
              </svg>
            </span>
            <span class="theme-icon-dark">
              <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor" />
              </svg>
            </span>
          </a>
        </li> --}}
        <li class="nav-item dropdown custom-drop">
          <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ asset('assets/images/userblue.png') }}" alt="User-Profile"
              class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded rounded-circle">
            <div class="caption ms-3 d-none d-md-block ">
              <h6 class="mb-0 caption-title">{{ authUser()?->name ?? 'User' }}</h6>
              <p class="mb-0 caption-sub-title">{{ authUser()?->role?->name ?? 'Member' }}</p>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="{{ route('member.profile') }}">Profile</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="{{ route('member.logout') }}">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>