@extends('layout_plataforma.app', ['title_html' => 'Resultados - ' . $envio->problema->nombre, 'title' => ' Envio #' . $envio->id . ' - Problema ' . $envio->problema->nombre, "breadcrumbs"=>[["nombre"=>"Envios", "route"=>route("envios.listado")], ["nombre"=>"Envio #".$envio->id]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row">
            <div class="col-sm col-xs-12">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header">
                        Resultados
                    </div>
                    <div class="card-body">
                        <div class="accordion accordion-flush" id="resultados_accord">
                            @for ($i = 0; $i < count($evaluaciones); $i++)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#resultados_{{ $i }}" aria-expanded="false"
                                            aria-controls="resultados_{{ $i }}"
                                            id="evaluacion_{{$i}}_button">
                                            @if($evaluaciones[$i]->estado=="En Proceso") 
                                            Caso de Prueba #{{ $i + 1 }}: <span class="mx-2 badge text-bg-warning" id="badge_estado_{{$i}}">In Process</span><span id="informacion_span_{{$i}}"></span>
                                            @else Caso de Prueba #{{ $i + 1 }}:<span class="mx-2 badge {{ $evaluaciones[$i]->estado == 'Aceptado' ? 'text-bg-success' : 'text-bg-danger' }}">{{ $evaluaciones[$i]->resultado }}</span>
                                            - Tiempo: {{ $evaluaciones[$i]->tiempo ? $evaluaciones[$i]->tiempo : '0' }}
                                            segundos - Memoria:
                                            {{ $evaluaciones[$i]->memoria ? $evaluaciones[$i]->memoria : '0' }} KB
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="resultados_{{ $i }}" class="accordion-collapse collapse"
                                        data-bs-parent="#resultados_accord">
                                        <div class="accordion-body">
                                            <div class="d-flex flex-row mb-3">
                                                @if (!isset($res_certamen) && isset($evaluaciones[$i]->casos_pruebas->entradas) && $evaluaciones[$i]->casos_pruebas->ejemplo == true)
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Entradas</h5>
                                                            <p class="card-text"  id="evaluacion_{{$i}}_e">
                                                                {!! nl2br($evaluaciones[$i]->casos_pruebas->entradas) !!}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (!isset($res_certamen) && isset($evaluaciones[$i]->casos_pruebas->salidas) && $evaluaciones[$i]->casos_pruebas->ejemplo == true)
                                                    <div class="card mx-2">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Salidas Esperadas</h5>
                                                            <p class="card-text" id="evaluacion_{{$i}}_se">{!! nl2br($evaluaciones[$i]->casos_pruebas->salidas) !!}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div
                                                    class="card {{ $evaluaciones[$i]->estado == 'Rechazado' || $evaluaciones[$i]->estado == 'Error' ? 'border-danger' : 'border-success' }}" id="card_evaluacion_{{$i}}">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Salidas</h5>
                                                        <p class="card-text" id="evaluacion_{{$i}}_s">
                                                            {!! $evaluaciones[$i]->stout ? nl2br(base64_decode($evaluaciones[$i]->stout)) : '-' !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                                <div class="card border-danger @if (!isset($evaluaciones[$i]->error_compilacion)) d-none @endif" id="card_error_compilacion_{{$i}}">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Mensaje del Compilador</h5>
                                                        <p class="card-text text-danger"  id="evaluacion_{{$i}}_ec">
                                                            {!! $evaluaciones[$i]->error_compilacion ? nl2br(base64_decode($evaluaciones[$i]->error_compilacion)) : '-' !!}</p>
                                                    </div>
                                                </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12 mt-xs-2">
                <div class="card border-danger">
                    <div class="card-body px-5">
                        <h6 class="text-center">Tiempo en Total:</h6>
                        <h5 id="tiempo" class="text-center"></h5>
                            <div class="d-flex flex-column align-items-center @if (isset($res_certamen) || $envio->usuario->id != auth()->user()->id || $evaluaciones->contains('estado', '=', 'En Proceso') || $envio->solucionado == true ||$tieneRetroalimentacion == true) d-none @endif" id="div_btn_retroalimentacion">
                                <a class="btn btn-primary btn-block {{ $problema->habilitar_llm == true && $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                                    href="{{ route('envios.generar_retroalimentacion', ['token' => $envio->token]) }}"
                                    role="button" onclick="solicitarRetroalimentacion(event)" id="boton_ra"> <img src="{{asset('img/AlienitoPensativo.png')}}" style="width:65px" class="me-3"><span class="align-middle"> {{ $problema->habilitar_llm == true && $cant_retroalimentacion > 0 ? 'Solicitar Ayuda' : 'Ayuda no disponible' }}</span></a>
                                    <strong class="text-center mt-2">Cantidad de Ayuda Disponible: {{$cant_retroalimentacion}}</strong>
                            </div>
                            <div class="d-flex flex-column align-items-center @if(isset($res_certamen) || $envio->usuario->id != auth()->user()->id || $tieneRetroalimentacion == false) d-none @endif" id="div_btn_ver_ayuda">
                                <a class="btn btn-primary text-nowrap btn-block"
                                    href="{{$tieneRetroalimentacion == true? route('envios.retroalimentacion', ['token' => $envio->token]) : "#" }}"
                                    role="button"><img src="{{asset('img/AlienitoPensativo.png')}}" style="width:65px" class="me-3">Ver Ayuda</a>
                            </div>
                        <hr>
                        @if ($envio->usuario->id == auth()->user()->id && !$evaluaciones->contains('estado', '=', 'En Proceso') && $envio->solucionado == false)
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-block"
                                    href="{{ isset($res_certamen)? route('certamenes.resolver_problema', ['token_certamen'=>$res_certamen->token, 'codigo'=>$envio->problema->codigo, 'id_curso'=>$res_certamen->certamen->id_curso]) : route('problemas.resolver', ['codigo' => $envio->problema->codigo, 'id_curso' => $envio->curso->id]) }}"
                                    role="button">Volver al intento</a>
                            </div>
                        @endif
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block @if(isset($res_certamen) && $res_certamen->finalizado) disabled @endif"
                                href="{{ isset($res_certamen)? route('certamenes.resolucion', ["token"=>$res_certamen->token]) : route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $envio->curso->id]) }}"
                                role="button">{{isset($res_certamen)? "Volver a la Evaluación   " : "Volver al Enunciado"}}</a>
                        </div>
                        @can('ver informe del problema')
                            @if(!isset($res_certamen))
                                <div class="row px-5 mt-2">
                                    <a class="btn btn-outline-secondary btn-sm btn-block"
                                        href="{{ route('informe.envios.problema', ['id_curso' => $envio->curso->id, 'id_problema' => $envio->problema->id]) }}"
                                        role="button">Volver al informe de envios del problema</a>
                                </div>
                            @elseif(isset($res_certamen) && $res_certamen->finalizado == true)
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-secondary btn-sm btn-block"
                                    href="{{route('informe.certamen.detalle', ['id_certamen'=>$res_certamen->id_certamen, 'id_res_certamen'=>$res_certamen->id])}}"
                                    role="button">Volver al informe de envios de la Evaluación</a>
                                </div>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3 mx-2">
        <div class="col">
            <div class="card border-danger" style="height:100%">
                <div class="card-header">
                    Código Fuente
                </div>
                <div class="card-body">
                    <pre><code class="{{ $highlightjs_choice }}-html">{{ $envio->codigo }}</code></pre>
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
        const tiempo_desarrollo = {{ $diferencia }}
        const string_tiempo_desarrollo = new Date(tiempo_desarrollo * 1000).toISOString().slice(11, 19);
        const esUsuario = @json($envio->usuario->id == auth()->user()->id);
        const esCertamen = @json(isset($res_certamen));
        const tieneRetroalimentacion = @json($tieneRetroalimentacion);
        const tiempo = document.getElementById("tiempo")
        const ruta_actualizacion = "{{route('envio.get_update', ["token"=>$envio->token])}}";
        const boton_ayuda = document.getElementById('div_btn_ver_ayuda');
        const boton_retroalimentacion = document.getElementById('div_btn_retroalimentacion');
        var evaluaciones_pendientes = {{$pendientes}};
        tiempo.innerHTML = string_tiempo_desarrollo

        let actualizacion_estado = setInterval(() => {
            if(evaluaciones_pendientes==0){
                clearInterval(actualizacion_estado)
            }else{
                fetch(ruta_actualizacion, {
                method: 'GET', 
                headers: {
                'Content-Type': 'application/json',
                },
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(result) {
                    for(var i=0; i<result.length;i++){
                        let button_evaluacion = document.getElementById('evaluacion_'+i+'_button');
                        let texto_salidas = document.getElementById('evaluacion_'+i+'_s');
                        let texto_error_compilacion = document.getElementById('evaluacion_'+i+'_ec');
                        let card_evaluacion = document.getElementById('card_evaluacion_'+i);
                        let badge_estado = document.getElementById('badge_estado_'+i);
                        if(result[i]["estado"]=="Rechazado" || result[i]["estado"]=="Error"){
                            badge_estado.classList.remove('text-bg-warning')
                            badge_estado.classList.add('text-bg-danger')
                            card_evaluacion.classList.add('border-danger')
                            evaluaciones_pendientes = evaluaciones_pendientes - 1;
                            if(tieneRetroalimentacion==true && esUsuario == true && esCertamen==false){
                                if(boton_ayuda.classList.contains('d-none')){
                                    boton_ayuda.classList.toggle('d-none');
                                }
                            }else if (tieneRetroalimentacion==false && esUsuario == true && esCertamen==false){
                                if(boton_retroalimentacion.classList.contains('d-none')){
                                    boton_retroalimentacion.classList.toggle('d-none');
                                }
                            }
                        }else if(result[i]["estado"]=="Aceptado"){
                            badge_estado.classList.remove('text-bg-warning')
                            badge_estado.classList.add('text-bg-success')
                            card_evaluacion.classList.add('border-success')
                            evaluaciones_pendientes = evaluaciones_pendientes - 1;
                        }
                        if(result[i]["memoria"]!=null && result[i]["tiempo"]!=null){
                            document.getElementById("informacion_span_"+i).innerHTML = "- Tiempo: "+result[i]["tiempo"]+" segundos - Memoria: "+result[i]["memoria"]+" KB";
                        }
                        badge_estado.innerHTML = result[i]["resultado"]!=null? result[i]["resultado"] : "In Process"
                        if(result[i]["stout"]!=null){
                            texto_salidas.innerHTML = result[i]["stout"]
                            if(result[i]["estado"]=="Rechazado" || result[i]["estado"]=="Error"){
                                card_evaluacion.classList.add('border-danger')
                            }else if(result[i]["estado"]=="Aceptado"){
                                card_evaluacion.classList.add('border-success')
                            }
                        }
                        if(result[i]["error_compilacion"]!=null){
                            texto_error_compilacion.innerHTML = result[i]["error_compilacion"];
                            document.getElementById('card_error_compilacion_'+i).classList.remove('d-none');
                        }
                    }
                })
                .catch(function(error) {
                    clearInterval(actualizacion_estado);
                });
            }
        }, 2000);
    </script>
@endpush
