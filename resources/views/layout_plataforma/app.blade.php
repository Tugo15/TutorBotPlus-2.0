<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title_html ? $title_html . ' - ' : '' }}Tutorbot+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('assets/css/fontawesome/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fontawesome/v4-shims.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fontawesome/v5-font-face.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/plataforma.css') }}?v={{ @filemtime(public_path('assets/css/plataforma.css')) ?: '1.0' }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatables_argon_fix.css') }}?v={{ @filemtime(public_path('assets/css/datatables_argon_fix.css')) ?: '1.0' }}">
    <script src="{{ asset('assets/js/disable_devtools.js') }}"></script>
    <script src="{{ mix('js/plataforma.js') }}" defer></script>
    <link rel="stylesheet" href="{{ mix('css/plataforma.css') }}">
    <style>
        /* roboto-regular - latin */
        @font-face {
            font-display: swap;
            /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 400;
            src: url("{{asset('fonts/roboto-v32-latin-regular.eot')}}");
            /* IE9 Compat Modes */
            src: url('{{asset("fonts/roboto-v32-latin-regular.eot")}}?#iefix') format('embedded-opentype'),
                /* IE6-IE8 */
                url('{{asset("fonts/roboto-v32-latin-regular.woff2")}}') format('woff2'),
                /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
                url('{{asset("fonts/roboto-v32-latin-regular.woff")}}') format('woff'),
                /* Chrome 5+, Firefox 3.6+, IE 9+, Safari 5.1+, iOS 5+ */
                url('{{asset("fonts/roboto-v32-latin-regular.ttf")}}') format('truetype'),
                /* Chrome 4+, Firefox 3.5+, IE 9+, Safari 3.1+, iOS 4.2+, Android Browser 2.2+ */
                url('{{asset("fonts/roboto-v32-latin-regular.svg")}}#Roboto') format('svg');
            /* Legacy iOS */
        }
        body, h1, h2, h3, h4, h5, h6, p, a, input, button, select, table, th, td, label {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", "Open Sans", "Helvetica Neue", Arial, sans-serif !important;
        }

        /* PROTECCIÓN ABSOLUTA PARA ÍCONOS */
        i.fa, .fa, i.fas, .fas, .fa-solid, [class*=" fa-"], [class^="fa-"] {
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
    </style>
    @stack('css')
</head>

<body style="background-color: #f2f2f2;">
    @include('layout_plataforma.navbar')
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between align-items-center px-5">
            <h3>{{ $title ? $title : 'No Definido' }}</h3>
            @if(!isset($res_certamen))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/')}}">Inicio</a></li>
                    @foreach($breadcrumbs as $key=>$item)
                    <li class="breadcrumb-item @if($key == sizeof($breadcrumbs)) active @endif" aria-current="page"> @if(isset($item["route"]))<a href="{{$item["route"]}}">{{$item["nombre"]}}</a> @else {{$item["nombre"]}} @endif </li>
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>
    </div>
    <main class="main-content">
        @yield('content')
    </main>
    @stack('js')
</body>


</html>
