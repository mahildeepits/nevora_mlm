<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light" data-bs-theme-color="theme-color-default" id="member-layout">

<head>
  <x-hlinks />
</head>

<body class="  ">
  <x-loader />

  <x-sidebar />

  <main class="main-content">
    <div class="position-relative iq-banner">
      <x-navbar />
      <x-header />
    </div>

    <div class="container-fluid content-inner py-0">
      @yield('content')
    </div>

    <x-footer />
  </main>

  {{-- Mobile/Tablet offcanvas overlay backdrop --}}
  <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

  <x-fscripts />
  <x-common.modal />
  @stack('modals')
</body>

</html>