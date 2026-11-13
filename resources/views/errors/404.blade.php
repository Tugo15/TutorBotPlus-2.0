<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada (404) - TutorBot+</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    <link href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fontawesome/all.min.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100 d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card shadow-sm border-0 text-center p-4 p-md-5" style="max-width: 500px; width: 100%;">
        <div class="mb-3">
            <img src="{{ asset('img/ico_tutorbot.png') }}" alt="TutorBot+" style="width: 90px; height: auto;">
        </div>
        <h1 class="display-4 font-weight-bold text-primary mb-1">404</h1>
        <h5 class="font-weight-bold text-dark mb-2">Página no encontrada</h5>
        <p class="text-sm text-secondary mb-4">
            Lo sentimos, la ruta o recurso que estás buscando no existe o fue movido.
        </p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/') }}'" class="btn btn-sm btn-outline-secondary mb-0">
                <i class="fa fa-arrow-left me-1"></i> Volver atrás
            </button>
            <a href="{{ url('/') }}" class="btn btn-sm btn-primary mb-0">
                <i class="fa fa-home me-1"></i> Ir al Inicio
            </a>
        </div>
    </div>
</body>
</html>

