@extends('layout_plataforma.app', ['title_html' => 'Retroalimentación para Envío #' . $retroalimentacion->id_envio, 'title' => 'Retroalimentación - Envío #' . $retroalimentacion->id_envio, "breadcrumbs"=>[["nombre"=>"Envíos", "route"=>route("envios.listado")], ["nombre"=>"Envío #".$envios->id, "route"=>route('envios.ver', ['token'=>$envios->token])], ["nombre"=>"Retroalimentación"]]])
@section('content')
    <div class="container-fluid py-3 px-3 px-md-4">
        <div class="row gy-3">
            <div class="col-md-8 col-12">
                <div class="card border-danger overflow-auto" style="min-height: 20rem; max-height: 35rem;">
                    <div class="card-header">
                        Retroalimentación
                    </div>
                    <div class="card-body">
                        {!! Str::markdown($retroalimentacion->retroalimentacion) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card border-danger h-100">
                    <div class="card-body px-3 px-md-4 d-flex flex-column justify-content-center align-items-center gap-2">
                        <div class="text-center mb-2">
                           <img src="{{asset('img/ico_tutorbot.png')}}" alt="Mascota TutorBot" class="img-thumbnail" style="max-width: 160px;">
                        </div>
                        <a class="btn btn-primary w-100 {{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                            href="{{ route('envios.generar_retroalimentacion', ['token' => $token]) }}" id="boton_ra"
                            role="button" onclick="solicitarRetroalimentacion(event)">{{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? 'Generar Nueva Ayuda (Disponible: ' . $cant_retroalimentacion . ')' : 'Ayuda no disponible' }}</a>
                        <a class="btn btn-outline-primary text-nowrap btn-sm w-100"
                            href="{{ route('envios.ver', ['token' => $token]) }}" role="button">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3 mx-2">
        <div class="col">
            <div class="card border-danger overflow-auto mb-5" style="height: 15rem;">
                <div class="card-header">
                    Código Fuente
                </div>
                <div class="card-body">
                    <pre style="height: 100%"><code class="{{ $highlightjs_choice }}-html" >{{ $envios->codigo }}</code></pre>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="{{ asset('assets/js/highlightjs/styles/dark.css') }}">
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script>
    <script src="{{ asset('assets/js/highlightjs/highlight.min.js') }}" type="text/javascript"></script>
    <script>
        hljs.highlightAll();
    </script>
@endpush
