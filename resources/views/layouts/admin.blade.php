<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light" data-bs-theme-color="theme-color-default" class="admin-layout"
  id="admin-layout">

<head>
  <link rel="stylesheet" href="{{ asset('assets/css/astyle.css') }}?{{ rand(000000000, 999999999) }}">
  <x-hlinks />
</head>

<body class="  ">
  <span class="screen-darken"></span>
  <x-loader />

  <main class="main-content">
    <x-admin-navbar />
    <x-horizontal-menu />

    <div class="conatiner-fluid content-inner pb-0">
      @yield('content')
    </div>

    <x-footer />
  </main>

  <x-fscripts />
  <x-common.modal />
  @stack('modals')
</body>

</html>