<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title_html ? $title_html . ' - ' : '' }}Tutorbot+</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plataforma.css') }}" rel="stylesheet">
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
        body{
            font-family: Roboto;
            
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
