@extends('layout_plataforma.app', ['title_html' => 'Retroalimentación para Envio #' . $retroalimentacion->id_envio, 'title' => 'Retroalimentación - Envio #' . $retroalimentacion->id_envio, "breadcrumbs"=>[["nombre"=>"Envios", "route"=>route("envios.listado")], ["nombre"=>"Envio #".$envios->id, "route"=>route('envios.ver', ['token'=>$envios->token])], ["nombre"=>"Retroalimentación"]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="row">
            <div class="col-sm col-xs-12">
                <div class="card border-danger overflow-auto" style="height: 35rem;">
                    <div class="card-header">
                        Retroalimentación
                    </div>
                    <div class="card-body">
                        {!! Str::markdown($retroalimentacion->retroalimentacion) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger" style="height:100%">
                    <div class="card-body px-5">
                        <div class="row px-5">
                           <img src="{{asset('img/ico_tutorbot.png')}}" alt="Mascota TutorBot" class="img-thumbnail mb-2">
                        </div>
                        <div class="row px-5">
                            <a class="btn btn-primary btn-block {{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                                href="{{ route('envios.generar_retroalimentacion', ['token' => $token]) }}" id="boton_ra"
                                role="button" onclick="solicitarRetroalimentacion(event)">{{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? 'Generar Nueva Ayuda (Cantidad Disponible: ' . $cant_retroalimentacion . ')' : 'Ayuda no disponible' }}</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-primary text-nowrap btn-sm btn-block"
                                href="{{ route('envios.ver', ['token' => $token]) }}" role="button">Volver</a>
                        </div>
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
