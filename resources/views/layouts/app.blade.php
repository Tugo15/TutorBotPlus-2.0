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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="{{asset('assets/js/fontawesome/all.min.js')}}" crossorigin="anonymous"></script>
    <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
    <!-- CSS Files -->
    <link href="{{asset('assets/css/fontawesome/all.min.css')}}" rel="stylesheet">
    <script src="{{ asset('assets/js/disable_devtools.js') }}"></script>
    <script src="{{ mix('js/app.js') }}" defer></script>
    <link rel="stylesheet" href="{{ mix('assets/css/argon-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatables_argon_fix.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/selection_cards.css') }}">

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
    <link rel="stylesheet" href="{{ asset('assets/css/datatables_argon_fix.css') }}">
    <style>
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
            position: relative !important;
            padding-right: 28px !important;
            cursor: pointer !important;
        }

        table.dataTable thead th.sorting:after,
        table.dataTable thead th.sorting_asc:after,
        table.dataTable thead th.sorting_desc:after {
            position: absolute !important;
            right: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            opacity: 0.5 !important;
        }

        table.dataTable thead th.sorting_asc:after,
        table.dataTable thead th.sorting_desc:after {
            opacity: 1 !important;
            color: #5e72e4 !important;
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
