@extends('layout_plataforma.app', ['title_html' => 'Resultados - ' . $envio->problema->nombre, 'title' => ' Envio #' . $envio->id . ' - Problema ' . $envio->problema->nombre, "breadcrumbs"=>[["nombre"=>"Envios", "route"=>route("envios.listado")], ["nombre"=>"Envio #".$envio->id]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row">
            <div class="col-sm col-xs-12">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Resultados de la Evaluación</span>
                        <span class="badge {{ $envio->solucionado ? 'bg-success' : 'bg-secondary' }}">
                            Puntaje: {{ $envio->puntaje ?? 0 }} pts ({{ $envio->cant_casos_resuelto ?? 0 }}/{{ count($evaluaciones) }} casos)
                        </span>
                    </div>
                    <div class="card-body">
                        @php
                            $tieneEjemplos = $evaluaciones->contains(fn($ev) => (bool)($ev->casos_pruebas->ejemplo ?? false));
                            // Casos visibles: los marcados como ejemplo (o el primer caso si ninguno tiene la marca de ejemplo)
                            $evaluacionesVisibles = $evaluaciones->filter(function($ev, $idx) use ($tieneEjemplos) {
                                return $tieneEjemplos ? (bool)($ev->casos_pruebas->ejemplo ?? false) : ($idx === 0);
                            });

                            $evaluacionesOcultas = $evaluaciones->reject(function($ev, $idx) use ($tieneEjemplos) {
                                return $tieneEjemplos ? (bool)($ev->casos_pruebas->ejemplo ?? false) : ($idx === 0);
                            });

                            $totalOcultos = $evaluacionesOcultas->count();
                            $aceptadosOcultos = $evaluacionesOcultas->filter(fn($e) => $e->estado === 'Aceptado')->count();
                            $falladosOcultos = $evaluacionesOcultas->filter(fn($e) => $e->estado !== 'Aceptado' && $e->estado !== 'En Proceso');
                            $enProcesoOcultos = $evaluacionesOcultas->filter(fn($e) => $e->estado === 'En Proceso')->count();
                        @endphp

                        {{-- LISTA DE CASOS VISIBLES (EJEMPLOS) --}}
                        <div class="accordion accordion-flush" id="resultados_accord">
                            @foreach ($evaluacionesVisibles as $evVisible)
                                @php
                                    $i = $evaluaciones->search(fn($item) => $item->id == $evVisible->id);
                                    if ($i === false) $i = $loop->index;
                                @endphp
                                <div class="accordion-item mb-2 border rounded">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#resultados_{{ $i }}" aria-expanded="false"
                                            aria-controls="resultados_{{ $i }}"
                                            id="evaluacion_{{$i}}_button">
                                            @if($evVisible->estado=="En Proceso") 
                                                Caso Visible #{{ $loop->iteration }}: <span class="mx-2 badge text-bg-warning" id="badge_estado_{{$i}}">In Process</span><span id="informacion_span_{{$i}}"></span>
                                            @else 
                                                Caso Visible #{{ $loop->iteration }}: <span class="mx-2 badge {{ $evVisible->estado == 'Aceptado' ? 'text-bg-success' : 'text-bg-danger' }}" id="badge_estado_{{$i}}">{{ $evVisible->resultado }}</span>
                                                <span id="informacion_span_{{$i}}">- Tiempo: {{ $evVisible->tiempo ? $evVisible->tiempo : '0' }} seg - Memoria: {{ $evVisible->memoria ? $evVisible->memoria : '0' }} KB</span>
                                                @if(isset($problema->restricciones) && trim($problema->restricciones) !== '' && !is_null($envio->cumple_restricciones))
                                                    <span class="mx-2 badge {{ $envio->cumple_restricciones ? 'text-bg-success' : 'text-bg-danger' }}">{{ $envio->cumple_restricciones ? 'Cumple' : 'No cumple' }}</span>
                                                @endif
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="resultados_{{ $i }}" class="accordion-collapse collapse"
                                        data-bs-parent="#resultados_accord">
                                        <div class="accordion-body">
                                            @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
                                                <div class="alert alert-sm {{ $envio->cumple_restricciones ? 'alert-success border-success' : 'alert-danger border-danger' }} py-2 px-3 mb-3" style="font-size: 0.85rem;">
                                                    <strong>Restricción ({{ $problema->restricciones }}):</strong>
                                                    @if(!is_null($envio->cumple_restricciones))
                                                        <span class="badge {{ $envio->cumple_restricciones ? 'bg-success' : 'bg-danger' }} ms-1">
                                                            {{ $envio->cumple_restricciones ? 'Cumple' : 'No cumple' }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark ms-1">Pendiente de verificación</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="d-flex flex-row mb-3 flex-wrap gap-2">
                                                @if (!isset($res_certamen) && isset($evVisible->casos_pruebas->entradas))
                                                    <div class="card flex-grow-1" style="min-width: 140px;">
                                                        <div class="card-body p-3">
                                                            <h6 class="card-title text-xs font-weight-bold">Entradas</h6>
                                                            <p class="card-text font-monospace text-xs mb-0" id="evaluacion_{{$i}}_e">
                                                                {!! nl2br(e($evVisible->casos_pruebas->entradas)) !!}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (!isset($res_certamen) && isset($evVisible->casos_pruebas->salidas))
                                                    <div class="card flex-grow-1" style="min-width: 140px;">
                                                        <div class="card-body p-3">
                                                            <h6 class="card-title text-xs font-weight-bold">Salidas Esperadas</h6>
                                                            <p class="card-text font-monospace text-xs mb-0" id="evaluacion_{{$i}}_se">{!! nl2br(e($evVisible->casos_pruebas->salidas)) !!}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="card flex-grow-1 {{ $evVisible->estado == 'Rechazado' || $evVisible->estado == 'Error' ? 'border-danger' : 'border-success' }}" style="min-width: 140px;" id="card_evaluacion_{{$i}}">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title text-xs font-weight-bold">Salidas Obtenidas</h6>
                                                        <p class="card-text font-monospace text-xs mb-0" id="evaluacion_{{$i}}_s">
                                                            {!! $evVisible->stout ? nl2br(e(base64_decode($evVisible->stout))) : '-' !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card border-danger @if (!isset($evVisible->error_compilacion)) d-none @endif" id="card_error_compilacion_{{$i}}">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title text-danger text-xs font-weight-bold">Mensaje del Compilador</h6>
                                                    <p class="card-text text-danger font-monospace text-xs mb-0" id="evaluacion_{{$i}}_ec">
                                                        @php
                                                            $ecRaw = $evVisible->error_compilacion ?? '';
                                                            $ecDecoded = base64_decode($ecRaw, true);
                                                            $ecTexto = ($ecDecoded !== false && base64_encode($ecDecoded) === $ecRaw && mb_check_encoding($ecDecoded, 'UTF-8')) ? $ecDecoded : $ecRaw;
                                                        @endphp
                                                        {!! $ecTexto ? nl2br(e($ecTexto)) : '-' !!}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- RESUMEN COMPACTO DE CASOS DE PRUEBA DE EVALUACIÓN (OCULTOS) --}}
                        @if($totalOcultos > 0)
                            @if($enProcesoOcultos > 0)
                                <div class="alert alert-warning py-2 px-3 mt-3 d-flex align-items-center" id="alert_ocultos_proceso">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    <span class="text-xs">Evaluando {{ $totalOcultos }} casos de prueba de evaluación...</span>
                                </div>
                            @elseif($falladosOcultos->isEmpty())
                                <div class="alert alert-success border-success py-2 px-3 mt-3 d-flex align-items-center justify-content-between shadow-xs">
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-check-circle fa-lg text-success me-2"></i>
                                        <strong class="text-xs text-dark">Casos de prueba de evaluación:</strong>
                                        <span class="badge bg-success ms-2">{{ $aceptadosOcultos }} de {{ $totalOcultos }} casos aceptados</span>
                                    </div>
                                    <span class="badge text-bg-success">100% Superado</span>
                                </div>
                            @else
                                <div class="card border-danger mt-3 shadow-xs">
                                    <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-times-circle text-danger me-2"></i>
                                            <strong class="text-xs text-dark">Casos de prueba de evaluación:</strong>
                                            <span class="badge bg-success ms-2">{{ $aceptadosOcultos }} aceptados</span>
                                            <span class="badge bg-danger ms-1">{{ $falladosOcultos->count() }} con error</span>
                                        </div>
                                        <span class="text-xxs text-muted">Resumen compacto</span>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 250px; overflow-y: auto;">
                                        @foreach($falladosOcultos as $evOculta)
                                            @php
                                                $idxReal = $evaluaciones->search(fn($x) => $x->id == $evOculta->id);
                                                $numCaso = ($idxReal !== false) ? $idxReal + 1 : $loop->iteration;
                                                $ecRaw = $evOculta->error_compilacion ?? '';
                                                $ecDecoded = base64_decode($ecRaw, true);
                                                $ecTexto = ($ecDecoded !== false && base64_encode($ecDecoded) === $ecRaw && mb_check_encoding($ecDecoded, 'UTF-8')) ? $ecDecoded : $ecRaw;
                                            @endphp
                                            <div class="py-1 px-2 border-bottom d-flex flex-column">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-xs font-weight-bold text-danger">
                                                        <i class="fa fa-times me-1"></i> Error en el Caso #{{ $numCaso }}
                                                    </span>
                                                    <span class="badge text-bg-danger">
                                                        {{ $evOculta->resultado ?? $evOculta->estado }}
                                                    </span>
                                                </div>
                                                @if(!empty(trim($ecTexto)))
                                                    <small class="text-xxs text-danger font-monospace mt-1 bg-light p-1 rounded">
                                                        {{ Str::limit(trim($ecTexto), 200) }}
                                                    </small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12 mt-xs-2">
                <div class="card border-danger">
                    <div class="card-body px-5">
                        <h6 class="text-center">Tiempo en Total:</h6>
                        <h5 id="tiempo" class="text-center"></h5>
                        @if ($problema->habilitar_llm)
                            <div class="d-flex flex-column align-items-center @if (isset($res_certamen) || $envio->usuario->id != auth()->user()->id || $evaluaciones->contains('estado', '=', 'En Proceso') || $envio->solucionado == true ||$tieneRetroalimentacion == true || $cant_retroalimentacion <= 0) d-none @endif" id="div_btn_retroalimentacion">
                                <a class="btn btn-primary btn-block {{ $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                                    href="{{ route('envios.generar_retroalimentacion', ['token' => $envio->token]) }}"
                                    role="button" onclick="solicitarRetroalimentacion(event)" id="boton_ra"> 
                                    <img src="{{asset('img/AlienitoPensativo.png')}}" style="width:65px" class="me-3">
                                    <span class="align-middle">Solicitar Ayuda</span>
                                </a>
                                <strong class="text-center mt-2">Cantidad de Ayuda Disponible: {{$cant_retroalimentacion}}</strong>
                            </div>
                            <div class="d-flex flex-column align-items-center @if(isset($res_certamen) || $envio->usuario->id != auth()->user()->id || $tieneRetroalimentacion == false) d-none @endif" id="div_btn_ver_ayuda">
                                <a class="btn btn-primary text-nowrap btn-block"
                                    href="{{$tieneRetroalimentacion == true? route('envios.retroalimentacion', ['token' => $envio->token]) : "#" }}"
                                    role="button"><img src="{{asset('img/AlienitoPensativo.png')}}" style="width:65px" class="me-3">Ver Ayuda</a>
                            </div>
                        @endif
                        <hr>
                        @if ($envio->usuario->id == auth()->user()->id && !$evaluaciones->contains('estado', '=', 'En Proceso') && $envio->solucionado == false)
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-block"
                                    href="{{ (isset($res_certamen) && $res_certamen->certamen)? route('certamenes.resolver_problema', ['token_certamen'=>$res_certamen->token, 'codigo'=>$envio->problema->codigo, 'id_curso'=>$res_certamen->certamen->id_curso]) : route('problemas.resolver', ['codigo' => $envio->problema->codigo, 'id_curso' => $envio->curso->id]) }}"
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
    @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
        <div class="row mt-3 mx-2">
            <div class="col-12">
                <div class="card border-info shadow-xs">
                    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 text-dark font-weight-bold">
                            <i class="fa fa-robot text-info me-2"></i>Verificación de Restricciones del Bot
                        </h6>
                        @if(!is_null($envio->cumple_restricciones))
                            @if($envio->cumple_restricciones)
                                <span class="badge bg-success text-uppercase font-weight-bold px-3 py-2" style="font-size: 0.85rem;"><i class="fa fa-check-circle me-1"></i> Cumple</span>
                            @else
                                <span class="badge bg-danger text-uppercase font-weight-bold px-3 py-2" style="font-size: 0.85rem;"><i class="fa fa-times-circle me-1"></i> No cumple</span>
                            @endif
                        @else
                            <span class="badge bg-warning text-dark font-weight-bold px-3 py-2" style="font-size: 0.85rem;"><i class="fa fa-clock me-1"></i> Pendiente de verificación</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            <strong class="text-dark"><i class="fa fa-exclamation-triangle text-warning me-1"></i> Restricción:</strong>
                            <p class="mb-0 text-dark font-weight-bold mt-1">{{ $problema->restricciones }}</p>
                        </div>

                        @if(isset($envio->verificacion_restricciones))
                            <div class="p-3 rounded bg-gray-100 border">
                                <h6 class="font-weight-bold text-dark mb-2" style="font-size: 0.9rem;"><i class="fa fa-comment-dots text-primary me-2"></i>Evaluación del Bot:</h6>
                                <div class="text-dark" style="white-space: pre-wrap; font-size: 0.9rem; line-height: 1.5;">{!! e($envio->verificacion_restricciones) !!}</div>
                            </div>
                        @else
                            <p class="text-muted text-xs mb-2">Aún no se ha verificado si tu código cumple con las restricciones del problema.</p>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('envios.verificar_restricciones', ['token' => $envio->token]) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-robot me-1"></i> {{ isset($envio->verificacion_restricciones) ? 'Volver a verificar con el Bot' : 'Verificar Restricciones con el Bot' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
        const habilitarLlm = @json((bool)$problema->habilitar_llm);
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
                    var finalizado = true;
                    for(var i=0; i<result.length;i++){
                        let button_evaluacion = document.getElementById('evaluacion_'+i+'_button');
                        let texto_salidas = document.getElementById('evaluacion_'+i+'_s');
                        let texto_error_compilacion = document.getElementById('evaluacion_'+i+'_ec');
                        let card_evaluacion = document.getElementById('card_evaluacion_'+i);
                        let badge_estado = document.getElementById('badge_estado_'+i);
                        let card_ec = document.getElementById('card_error_compilacion_'+i);

                        if(result[i]["estado"]=="En Proceso"){
                            finalizado = false;
                        }

                        if(badge_estado){
                            if(result[i]["estado"]=="Rechazado" || result[i]["estado"]=="Error"){
                                badge_estado.classList.remove('text-bg-warning', 'text-bg-success');
                                badge_estado.classList.add('text-bg-danger');
                                if(card_evaluacion) card_evaluacion.classList.add('border-danger');
                            }else if(result[i]["estado"]=="Aceptado"){
                                badge_estado.classList.remove('text-bg-warning', 'text-bg-danger');
                                badge_estado.classList.add('text-bg-success');
                                if(card_evaluacion) card_evaluacion.classList.add('border-success');
                            }
                            badge_estado.innerHTML = result[i]["resultado"]!=null? result[i]["resultado"] : "In Process";
                        }

                        let spanInfo = document.getElementById("informacion_span_"+i);
                        if(spanInfo && result[i]["memoria"]!=null && result[i]["tiempo"]!=null){
                            spanInfo.innerHTML = "- Tiempo: "+result[i]["tiempo"]+" seg - Memoria: "+result[i]["memoria"]+" KB";
                        }

                        if(texto_salidas && result[i]["stout"]!=null){
                            texto_salidas.innerHTML = result[i]["stout"];
                        }
                        if(texto_error_compilacion && result[i]["error_compilacion"]!=null){
                            texto_error_compilacion.innerHTML = result[i]["error_compilacion"];
                            if(card_ec) card_ec.classList.remove('d-none');
                        }
                    }

                    if(finalizado){
                        clearInterval(actualizacion_estado);
                        // Si se procesaban casos en segundo plano, recargamos la página para actualizar el resumen compacto
                        setTimeout(function(){ location.reload(); }, 1200);
                    }
                })
                .catch(function(error) {
                    clearInterval(actualizacion_estado);
                });
            }
        }, 2000);
    </script>
@endpush
