<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
<!-- Favicon -->
<link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
<!-- Library / Plugin Css Build -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/css/core/libs.min.css') }}">
<!-- Aos Animation Css -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/vendor/aos/dist/aos.css') }}">
<!-- Hope Ui Design System Css -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/css/hope-ui.min1fc6.css?v=4.0.0') }}">
<!-- Custom Css -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/css/custom.min1fc6.css?v=4.0.0') }}">
<!-- Customizer Css -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/css/customizer.min1fc6.css?v=4.0.0') }}">
<!-- RTL Css -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/css/rtl.min1fc6.css?v=4.0.0') }}">
<!-- Toastr CSS -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/vendor/toastr/toastr.min.css') }}">
<!-- Bootstrap datatables -->
<link rel="stylesheet" href="{{ asset('template/hope-ui/vendor/bootstrap5/dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('template/hope-ui/vendor/bootstrap5/buttons.min.css') }}">
<!-- fontawesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- CUSTOM CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?{{ rand(000000000, 999999999) }}">
<!-- Theme Logic -->
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
    }
</script>
@stack('styles')