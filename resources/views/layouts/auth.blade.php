<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light" data-bs-theme-color="theme-color-default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('template/hope-ui/css/core/libs.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/hope-ui/css/hope-ui.min1fc6.css?v=4.0.0') }}">
    <link rel="stylesheet" href="{{ asset('template/hope-ui/css/custom.min1fc6.css?v=4.0.0') }}">
    <link rel="stylesheet" href="{{ asset('template/hope-ui/css/customizer.min1fc6.css?v=4.0.0') }}">
    <link rel="stylesheet" href="{{ asset('template/hope-ui/css/rtl.min1fc6.css?v=4.0.0') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root {
            --bs-primary: #1d4280;
        }

        .px-10 {
            padding-inline: 4rem;
        }

        .fs-xs {
            font-size: 12px !important;
        }

        #formArea {
            margin-inline: auto;
        }

        .auth-card.login-card {
            padding: 0 4.25rem !important;
        }

        .auth-title {
            font-size: 20px;
            text-align: center;
            margin-bottom: 0.2rem;
        }

        .auth-sub-title {
            font-size: 12px;
            text-align: center;
        }

        label.form-label {
            font-size: 12px;
            color: #0c1017ff;
        }

        .form-control {
            font-size: 13px;
            padding: 0.3rem 0.5rem;
            line-height: 1;
        }

        .input-group-text {
            padding: 10px 13px;
            background-color: #1d4280;
        }

        .input-group-text i {
            color: #fff !important;
        }

        .input-group-text i.toggleIcon {
            color: grey !important;
        }

        .form-check-input {
            border-color: grey;
        }

        /* Remove autofill background color */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }

        .invalid-feedback {
            font-size: 11px !important;
        }

        .form-control[readonly],
        .form-control[disabled] {
            background-color: #f3f3f3 !important;
            cursor: not-allowed !important;
        }
    </style>
    @stack('styles')
</head>

<body class=" " data-bs-spy="scroll" data-bs-target="#elements-section" data-bs-offset="0" tabindex="0">
    <x-loader />

    <div class="wrapper">
        @yield('content')
    </div>

    <!-- Library Bundle Script -->
    <x-fscripts />
</body>

</html>