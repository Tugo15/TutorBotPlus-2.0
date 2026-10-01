<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{asset('img/favicon.ico')}}">
    <title>
        @if(isset($title_url)){{$title_url}} - @endif TutorBot+
    </title>
    <!--     Fonts and icons     -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Nucleo Icons -->
    <link href="{{asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
    <!-- Font Awesome Icons (CDN confiable + fallback local para íconos/emojis garantizados) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{asset('assets/css/fontawesome/all.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/fontawesome/v4-shims.min.css')}}" rel="stylesheet">
    <!-- CSS Files -->
    <script src="{{ asset('assets/js/disable_devtools.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/css/argon-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatables_argon_fix.css') }}?v={{ @filemtime(public_path('assets/css/datatables_argon_fix.css')) ?: '1.0' }}">
    <link rel="stylesheet" href="{{ asset('assets/css/selection_cards.css') }}?v={{ @filemtime(public_path('assets/css/selection_cards.css')) ?: '1.0' }}">

    @stack('css')
</head>

<body class="{{ $class ?? '' }}">

    @guest
        @yield('content')
    @endguest

    @auth
        @if (in_array(request()->route()->getName(), ['sign-in-static', 'sign-up-static', 'login', 'register', 'recover-password', 'rtl', 'virtual-reality']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile', 'profile-static']))
                <div class="min-height-300 bg-primary position-absolute w-100"></div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav')
                <main class="main-content border-radius-lg">
                    @yield('content')
                </main>
        @endif
    @endauth

    <!--   Core JS Files   -->
    <script src="{{asset('assets/js/core/popper.min.js')}}"></script>
    <script src="{{asset('assets/js/core/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    @stack('js')
    <style>
        /* Definición robusta de NucleoIcons con ruta directa generada por asset() */
        @font-face {
            font-family: 'NucleoIcons';
            src: url("{{ asset('assets/fonts/nucleo-icons.woff2') }}") format('woff2'),
                 url("{{ asset('assets/fonts/nucleo-icons.woff') }}") format('woff'),
                 url("{{ asset('assets/fonts/nucleo-icons.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        /* Tipografía Open Sans aplicada únicamente a textos (excluyendo cualquier ícono) */
        body, h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, p, input, button, select, textarea, table, th, td, label {
            font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        /* PROTECCIÓN ABSOLUTA PARA ÍCONOS */
        i.ni, .ni, [class*=" ni-"], [class^="ni-"] {
            font-family: 'NucleoIcons' !important;
            font-style: normal !important;
            font-weight: normal !important;
            font-variant: normal !important;
            text-transform: none !important;
            line-height: 1 !important;
            display: inline-block !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }

        i.fa, .fa, i.fas, .fas, .fa-solid, [class*=" fa-"]:not(.ni), [class^="fa-"]:not(.ni) {
            font-family: "Font Awesome 6 Free", "FontAwesome" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            display: inline-block !important;
        }

        i.far, .far, .fa-regular {
            font-family: "Font Awesome 6 Free", "FontAwesome" !important;
            font-weight: 400 !important;
            font-style: normal !important;
            display: inline-block !important;
        }

        i.fab, .fab, .fa-brands {
            font-family: "Font Awesome 6 Brands", "FontAwesome" !important;
            font-weight: 400 !important;
            font-style: normal !important;
            display: inline-block !important;
        }

        /* Control de tamaño para íconos SVG y botones (elimina íconos gigantes y desalineados) */
        svg.svg-inline--fa,
        .svg-inline--fa {
            display: inline-block !important;
            height: 1em !important;
            width: 1em !important;
            max-width: 1.25em !important;
            max-height: 1.25em !important;
            vertical-align: -0.125em !important;
            overflow: visible !important;
        }

        .btn .svg-inline--fa,
        .btn i.fa,
        .btn i.ni {
            font-size: 0.75rem !important;
            vertical-align: middle !important;
        }

        .icon-shape i.ni,
        .icon-shape .svg-inline--fa {
            font-size: 1rem !important;
            line-height: 0 !important;
        }

        /* ABSOLUTE FIX FOR DATATABLES LENGTH SELECT ARROW OVERLAP */
        .dataTables_length label,
        div.dataTables_length label {
            display: inline-flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 0.5rem !important;
            margin-bottom: 0 !important;
            white-space: nowrap !important;
            font-size: 0.875rem !important;
            color: #6c757d !important;
            font-weight: 600 !important;
        }

        select[name$="_length"],
        .dataTables_length select,
        div.dataTables_length select,
        .dataTables_wrapper .dataTables_length select,
        html body div.dataTables_wrapper div.dataTables_length select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: calc(100% - 10px) center !important;
            background-size: 14px 12px !important;
            background-color: #ffffff !important;
            padding-left: 14px !important;
            padding-right: 42px !important; /* Huge 42px right padding */
            min-width: 95px !important;
            width: 95px !important;
            height: 38px !important;
            line-height: 1.5 !important;
            border-radius: 0.5rem !important;
            border: 1px solid #d2d6da !important;
            box-sizing: border-box !important;
            font-size: 0.875rem !important;
            color: #495057 !important;
            cursor: pointer !important;
            display: inline-block !important;
            margin: 0 0.5rem !important;
        }

        .dataTables_filter label,
        div.dataTables_filter label {
            display: inline-flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 0.5rem !important;
            margin-bottom: 0 !important;
            white-space: nowrap !important;
            font-size: 0.875rem !important;
            color: #6c757d !important;
            font-weight: 600 !important;
        }

        .dataTables_filter input,
        div.dataTables_filter input,
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid #d2d6da !important;
            margin-left: 0.5rem !important;
            outline: none !important;
            height: 38px !important;
        }

        /* DataTables Header Sorting Arrows Fix */
        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            cursor: pointer !important;
            padding-right: 10px !important; /* Normal padding, no huge gap */
        }

        table.dataTable thead th.sorting:before,
        table.dataTable thead th.sorting:after,
        table.dataTable thead th.sorting_asc:before,
        table.dataTable thead th.sorting_asc:after,
        table.dataTable thead th.sorting_desc:before,
        table.dataTable thead th.sorting_desc:after {
            position: static !important;
            display: inline-block !important;
            right: auto !important;
            top: auto !important;
            transform: none !important;
            margin-left: 3px !important;
            opacity: 0.3 !important;
        }

        table.dataTable thead th.sorting_asc:before,
        table.dataTable thead th.sorting_desc:after {
            opacity: 1 !important;
            color: #5e72e4 !important;
        }

        /* Fix ToastUI Editor Toolbar conflict with Bootstrap .table class */
        button.toastui-editor-toolbar-icons.table {
            width: 32px !important;
            height: 32px !important;
            margin-bottom: 0 !important;
            vertical-align: middle !important;
        }
    </style>

    <script>
        function toggleSelectionCards(containerId, state) {
            var container = document.getElementById(containerId);
            if (!container) return;
            var checkboxes = container.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(cb) {
                if (!cb.disabled) {
                    cb.checked = state;
                    var card = cb.closest('.selection-card');
                    if (card) card.classList.toggle('checked', state);
                }
            });
        }
    </script>
</body>

</html>
