@php
    $esDocenteOAdmin = auth()->check() && (
        auth()->user()->hasRole('administrador') ||
        auth()->user()->hasRole('profesor') ||
        auth()->user()->can('ver informe del certamen') ||
        auth()->user()->can('ver informe del curso') ||
        auth()->user()->can('ver informe del problema')
    );
    $certamenObj = (isset($res_certamen) && $res_certamen->certamen) ? $res_certamen->certamen : null;
    $certamenFinalizado = (isset($res_certamen) && $res_certamen->finalizado);
    $mostrarCasosCompletos = $esDocenteOAdmin || $certamenFinalizado || !isset($res_certamen);

    $breadcrumbs = [
        ['nombre' => 'Cursos', 'route' => route('cursos.listado')]
    ];
    if (isset($envio->curso)) {
        $breadcrumbs[] = ['nombre' => $envio->curso->nombre, 'route' => route('problemas.listado', ['id' => $envio->curso->id])];
    }
    if ($certamenObj) {
        if ($esDocenteOAdmin) {
            $breadcrumbs[] = ['nombre' => 'Informe ' . $certamenObj->nombre, 'route' => route('informe.certamen', ['id_certamen' => $certamenObj->id])];
        } else {
            $breadcrumbs[] = ['nombre' => $certamenObj->nombre, 'route' => route('certamenes.ver', ['id_certamen' => $certamenObj->id])];
        }
    }
    $breadcrumbs[] = ['nombre' => 'Envío #' . $envio->id . ' - ' . $problema->nombre];
@endphp
@extends('layout_plataforma.app', [
    'title_html' => 'Envío #' . $envio->id . ' - ' . $problema->nombre,
    'title' => 'Envío #' . $envio->id . ' - ' . $problema->nombre,
    'breadcrumbs' => $breadcrumbs
])

@push('css')
<style>
    #body_markdown img, .markdown-content img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin: 1.2rem auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .markdown-content pre {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.85rem;
    }
    .markdown-content table {
        width: 100%;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-collapse: collapse;
    }
    .markdown-content th, .markdown-content td {
        padding: 0.5rem 0.75rem;
        border: 1px solid #dee2e6;
    }
    .markdown-content th {
        background-color: #f8f9fa;
    }
    .meta-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.65rem;
    }
</style>
<link rel="stylesheet" href="{{ asset('assets/js/highlightjs/styles/dark.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    @include('components.alert')

    {{-- CABECERA PRINCIPAL: DATOS DEL ENVÍO Y ESTUDIANTE --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-gradient-primary text-white font-monospace">Envío #{{ $envio->id }}</span>
                        <h4 class="mb-0 text-dark font-weight-bold">{{ $problema->nombre }}</h4>
                        <span class="badge bg-light text-secondary border font-monospace">{{ $problema->codigo }}</span>
                    </div>
                    <div class="text-xs text-muted">
                        @if($certamenObj)
                            <span class="me-2 text-danger font-weight-bold"><i class="fa fa-award me-1"></i>Evaluación: {{ $certamenObj->nombre }}</span> &bull;
                        @endif
                        <span class="me-2"><i class="fa fa-graduation-cap me-1"></i>{{ $envio->curso->nombre ?? 'Sin curso' }}</span> &bull;
                        <span><i class="fa fa-calendar me-1"></i>{{ isset($envio->created_at) ? \Carbon\Carbon::parse($envio->created_at)->format('d/m/Y H:i:s') : '-' }}</span>
                    </div>
                </div>

                {{-- BADGE DE ESTADO GENERAL --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($envio->solucionado)
                        <span class="badge bg-success fs-6 py-2 px-3">
                            <i class="fa fa-check-circle me-1"></i> ACEPTADO ({{ $envio->puntaje ?? 0 }} pts)
                        </span>
                    @elseif($evaluaciones->contains('estado', '=', 'En Proceso'))
                        <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                            <i class="fa fa-spinner fa-spin me-1"></i> EN PROCESO
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 py-2 px-3">
                            <i class="fa fa-times-circle me-1"></i> {{ $envio->resultado ?? 'RECHAZADO' }} ({{ $envio->puntaje ?? 0 }} pts)
                        </span>
                    @endif
                </div>
            </div>

            {{-- DETALLES METADATA DEL ESTUDIANTE Y EJECUCIÓN --}}
            <div class="row g-2 pt-2 border-top">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex align-items-center p-2 rounded bg-gray-100 border">
                        <i class="fa fa-user-circle fa-2x text-primary me-2"></i>
                        <div class="overflow-hidden">
                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Estudiante</span>
                            <strong class="text-xs text-dark text-truncate d-block">{{ $envio->usuario->firstname }} {{ $envio->usuario->lastname }}</strong>
                            <small class="text-xxs text-muted font-monospace">RUT: {{ $envio->usuario->rut ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex align-items-center p-2 rounded bg-gray-100 border">
                        <i class="fa fa-code fa-2x text-info me-2"></i>
                        <div>
                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Lenguaje & IP</span>
                            <strong class="text-xs text-dark d-block">{{ $envio->lenguaje->nombre ?? 'Lenguaje' }}</strong>
                            <small class="text-xxs text-muted font-monospace">IP: {{ $envio->ip_origen ?? '127.0.0.1' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex align-items-center p-2 rounded bg-gray-100 border">
                        <i class="fa fa-tasks fa-2x text-success me-2"></i>
                        <div>
                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Casos Evaluados</span>
                            <strong class="text-xs text-dark d-block">{{ $envio->cant_casos_resuelto ?? 0 }} / {{ count($evaluaciones) }} superados</strong>
                            <small class="text-xxs text-muted">Puntaje total: {{ $envio->puntaje ?? 0 }} pts</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex align-items-center p-2 rounded bg-gray-100 border">
                        <i class="fa fa-stopwatch fa-2x text-warning me-2"></i>
                        <div>
                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Tiempo Desarrollo</span>
                            <strong class="text-xs text-dark d-block" id="tiempo">00:00:00</strong>
                            <small class="text-xxs text-muted">Duración del intento</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES DE RETORNO Y NAVEGACIÓN --}}
            @php
                $fromEnvios = request()->query('from') === 'envios' || \Illuminate\Support\Str::contains(url()->previous(), '/envios');
            @endphp
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-2 border-top">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($fromEnvios && !isset($res_certamen))
                        <a href="{{ route('envios.listado') }}" class="btn btn-sm btn-outline-secondary mb-0">
                            <i class="fa fa-arrow-left me-1"></i> Volver a Mis Envíos
                        </a>
                        <a href="{{ route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $envio->curso->id ?? null]) }}" class="btn btn-sm btn-outline-primary mb-0">
                            <i class="fa fa-file-text-o me-1"></i> Volver al Problema
                        </a>
                        @if($esDocenteOAdmin && isset($envio->curso))
                            <a href="{{ route('informe.envios.problema', ['id_curso' => $envio->curso->id, 'id_problema' => $problema->id]) }}" class="btn btn-sm btn-outline-info mb-0">
                                <i class="fa fa-chart-bar me-1"></i> Informe de Envíos
                            </a>
                        @endif
                    @elseif($esDocenteOAdmin && $certamenObj)
                        <a href="{{ route('informe.certamen', ['id_certamen' => $certamenObj->id]) }}" class="btn btn-sm btn-outline-primary mb-0">
                            <i class="fa fa-arrow-left me-1"></i> Volver al Informe de la Evaluación
                        </a>
                        @if($res_certamen)
                            <a href="{{ route('informe.certamen.detalle', ['id_certamen' => $certamenObj->id, 'id_res_certamen' => $res_certamen->id]) }}" class="btn btn-sm btn-outline-secondary mb-0">
                                <i class="fa fa-list me-1"></i> Ver Envíos del Estudiante
                            </a>
                        @endif
                    @elseif($esDocenteOAdmin && isset($envio->curso))
                        <a href="{{ route('informe.envios.problema', ['id_curso' => $envio->curso->id, 'id_problema' => $problema->id]) }}" class="btn btn-sm btn-outline-primary mb-0">
                            <i class="fa fa-arrow-left me-1"></i> Volver al Informe de Envíos
                        </a>
                    @else
                        @if(isset($res_certamen))
                            <a href="{{ route('certamenes.resolucion', ['token' => $res_certamen->token]) }}" class="btn btn-sm btn-outline-primary mb-0 @if($res_certamen->finalizado) disabled @endif">
                                <i class="fa fa-arrow-left me-1"></i> Volver a la Evaluación
                            </a>
                        @else
                            @if(request()->query('from') === 'envios' || \Illuminate\Support\Str::contains(url()->previous(), '/envios'))
                                <a href="{{ route('envios.listado') }}" class="btn btn-sm btn-outline-secondary mb-0">
                                    <i class="fa fa-arrow-left me-1"></i> Volver a Mis Envíos
                                </a>
                            @endif
                            <a href="{{ route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $envio->curso->id ?? null]) }}" class="btn btn-sm btn-outline-primary mb-0">
                                <i class="fa fa-file-text-o me-1"></i> Volver al Problema
                            </a>
                        @endif
                        @if($envio->usuario->id == auth()->id() && !$evaluaciones->contains('estado', '=', 'En Proceso'))
                            <a href="{{ (isset($res_certamen) && $res_certamen->certamen) ? route('certamenes.resolver_problema', ['token_certamen'=>$res_certamen->token, 'codigo'=>$envio->problema->codigo, 'id_curso'=>$res_certamen->certamen->id_curso]) : route('problemas.resolver', ['codigo' => $envio->problema->codigo, 'id_curso' => $envio->curso->id ?? null]) }}" class="btn btn-sm btn-primary mb-0">
                                <i class="fa fa-undo me-1"></i> {{ (isset($res_certamen) && $envio->solucionado) ? 'Modificar Solución' : 'Volver a Intentar' }}
                            </a>
                        @endif
                    @endif
                </div>

                {{-- ACCIONES DE RETROALIMENTACIÓN / AYUDA SI APLICA --}}
                @if($problema->habilitar_llm && $envio->usuario->id == auth()->id() && !isset($res_certamen))
                    <div class="d-flex align-items-center gap-2">
                        @if($tieneRetroalimentacion)
                            <a href="{{ route('envios.retroalimentacion', ['token' => $envio->token]) }}" class="btn btn-sm btn-info text-white mb-0">
                                <i class="fa fa-robot me-1"></i> Ver Ayuda del Bot
                            </a>
                        @elseif($cant_retroalimentacion > 0 && !$envio->solucionado)
                            <a href="{{ route('envios.generar_retroalimentacion', ['token' => $envio->token]) }}" id="boton_ra" onclick="solicitarRetroalimentacion(event)" class="btn btn-sm btn-primary mb-0">
                                <i class="fa fa-robot me-1"></i> Solicitar Ayuda ({{ $cant_retroalimentacion }} disponibles)
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SELECTOR DE VISTAS (DIVIDIDA / SOLO ENUNCIADO / SOLO ENVÍO) --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="btn-group shadow-xs" role="group">
            <button type="button" class="btn btn-sm btn-primary active" id="btn-vista-dividida" onclick="cambiarModoVista('dividida')">
                <i class="fa fa-columns me-1"></i> Vista Dividida (Enunciado y Envío)
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-vista-enunciado" onclick="cambiarModoVista('enunciado')">
                <i class="fa fa-file-text-o me-1"></i> Solo Enunciado
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-vista-envio" onclick="cambiarModoVista('envio')">
                <i class="fa fa-code me-1"></i> Solo Envío del Estudiante
            </button>
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL: COLUMNA ENUNCIADO + COLUMNA ENVÍO --}}
    <div class="row" id="row-principal">

        {{-- COLUMNA IZQUIERDA: ENUNCIADO DEL PROBLEMA --}}
        <div id="col-enunciado" class="col-lg-6 col-12 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger p-2"><i class="fa fa-book text-white fs-6"></i></span>
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold">Enunciado del Problema</h5>
                            <small class="text-muted">{{ $problema->nombre }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if(isset($problema->dificultad))
                            @php
                                $difClass = 'bg-success';
                                $dif = strtolower($problema->dificultad);
                                if(str_contains($dif, 'medio') || str_contains($dif, 'intermedio')) $difClass = 'bg-warning text-dark';
                                elseif(str_contains($dif, 'dificil') || str_contains($dif, 'avanzado')) $difClass = 'bg-danger text-white';
                            @endphp
                            <span class="badge {{ $difClass }} text-uppercase">{{ $problema->dificultad }}</span>
                        @endif
                        @if(isset($problema->categoria))
                            <span class="badge bg-light text-dark border">{{ $problema->categoria->nombre ?? '' }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4 text-wrap" id="body_markdown">
                    {{-- ALERTA DE RESTRICCIONES SI APLICA --}}
                    @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
                        <div class="alert alert-warning border-warning d-flex align-items-center mb-4" role="alert">
                            <i class="fa fa-exclamation-triangle text-warning me-2 fs-5"></i>
                            <div>
                                <strong>Restricción del Problema:</strong> {{ $problema->restricciones }}
                            </div>
                        </div>
                    @endif

                    {{-- CUERPO DEL ENUNCIADO EN MARKDOWN --}}
                    @php
                        $bodyProblemaSanitized = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $problema->body_problema ?? '');
                    @endphp
                    <div class="markdown-content text-dark" style="line-height: 1.65; font-size: 0.95rem;">
                        {!! Str::markdown($bodyProblemaSanitized, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => true,
                        ]) !!}
                    </div>

                    {{-- CASOS DE PRUEBA DE EJEMPLO --}}
                    @php
                        $casos_ejemplo = $problema->casos_de_prueba()->where('ejemplo', true)->orderBy('id', 'asc')->get();
                        if ($casos_ejemplo->isEmpty() && $problema->casos_de_prueba()->count() > 0 && $mostrarCasosCompletos) {
                            $casos_ejemplo = $problema->casos_de_prueba()->orderBy('id', 'asc')->take(1)->get();
                        }
                    @endphp

                    @if($casos_ejemplo->count() > 0)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fa fa-vials me-2"></i>Ejemplos de Entrada y Salida
                            </h6>
                            <div class="row">
                                @foreach($casos_ejemplo as $caso)
                                    <div class="col-12 mb-3">
                                        <div class="card bg-gray-100 border shadow-none">
                                            <div class="card-header py-1 px-3 bg-gray-200 d-flex justify-content-between align-items-center">
                                                <span class="text-xs font-weight-bold text-dark">Ejemplo #{{ $loop->iteration }}</span>
                                                @if(isset($caso->puntos))
                                                    <span class="text-xxs text-muted">{{ $caso->puntos }} pts</span>
                                                @endif
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    @if(!is_null($caso->entradas) && trim($caso->entradas) !== '')
                                                        <div class="col-md-6 mb-2 mb-md-0">
                                                            <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Entrada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->entradas }}</pre>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
                                                        </div>
                                                    @else
                                                        <div class="col-12">
                                                            <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: ENVÍO DEL ESTUDIANTE (CÓDIGO + CASOS + BOT) --}}
        <div id="col-envio" class="col-lg-6 col-12 mb-3">

            {{-- TARJETA 1: CÓDIGO FUENTE --}}
            <div class="card border-0 shadow-sm mb-3" id="card-codigo-fuente">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark text-sm">
                        <i class="fa fa-code text-primary me-2"></i> Código Fuente Entregado
                        <span class="badge bg-light text-dark border ms-2">{{ $envio->lenguaje->nombre ?? 'Código' }}</span>
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="copiarCodigo()">
                        <i class="fa fa-copy me-1"></i> Copiar Código
                    </button>
                </div>
                <div class="card-body p-0">
                    <pre class="mb-0 p-3" style="max-height: 420px; overflow-y: auto; background: #282c34; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;"><code class="{{ $highlightjs_choice }}-html" id="codigo_estudiante">{{ $envio->codigo }}</code></pre>
                </div>
            </div>

            {{-- TARJETA 2: VERIFICACIÓN DE RESTRICCIONES DEL BOT (SI TIENE) --}}
            @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
                <div class="card border-info shadow-sm mb-3">
                    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="mb-0 text-dark font-weight-bold text-xs">
                            <i class="fa fa-robot text-info me-2"></i> Verificación de Restricciones del Bot
                        </span>
                        @if(!is_null($envio->cumple_restricciones))
                            @if($envio->cumple_restricciones)
                                <span class="badge bg-success font-weight-bold px-2 py-1 text-xxs"><i class="fa fa-check-circle me-1"></i> CUMPLE</span>
                            @else
                                <span class="badge bg-danger font-weight-bold px-2 py-1 text-xxs"><i class="fa fa-times-circle me-1"></i> NO CUMPLE</span>
                            @endif
                        @else
                            <span class="badge bg-warning text-dark font-weight-bold px-2 py-1 text-xxs"><i class="fa fa-clock-o me-1"></i> Pendiente</span>
                        @endif
                    </div>
                    <div class="card-body p-3">
                        <div class="alert alert-light border py-2 px-3 mb-2" style="font-size: 0.82rem;">
                            <strong class="text-dark"><i class="fa fa-exclamation-triangle text-warning me-1"></i> Restricción:</strong>
                            <span class="text-dark font-weight-bold">{{ $problema->restricciones }}</span>
                        </div>
                        @if(isset($envio->verificacion_restricciones))
                            <div class="p-2 rounded bg-gray-100 border text-dark" style="white-space: pre-wrap; font-size: 0.82rem; line-height: 1.45;">
                                <strong class="d-block mb-1 text-primary"><i class="fa fa-comment-dots me-1"></i> Evaluación del Bot:</strong>
                                {!! e($envio->verificacion_restricciones) !!}
                            </div>
                        @else
                            <p class="text-muted text-xs mb-0">Aún no se ha verificado si el código cumple con las restricciones.</p>
                        @endif

                        @if($esDocenteOAdmin || $envio->usuario->id == auth()->id())
                            <div class="mt-2 text-end">
                                <a href="{{ route('envios.verificar_restricciones', ['token' => $envio->token]) }}" onclick="this.classList.add('disabled'); this.setAttribute('aria-disabled','true'); this.innerHTML='<i class=\'fa fa-spinner fa-spin me-1\'></i> Verificando...';" class="btn btn-xs btn-outline-info mb-0">
                                    <i class="fa fa-robot me-1"></i> Re-evaluar con el Bot
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- TARJETA 3: EVALUACIÓN DE CASOS DE PRUEBA --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="font-weight-bold text-dark text-sm">
                        <i class="fa fa-vials text-success me-2"></i> Evaluación de Casos de Prueba
                    </span>
                    <span class="badge {{ $envio->solucionado ? 'bg-success' : 'bg-secondary' }}">
                        {{ $envio->puntaje ?? 0 }} pts ({{ $envio->cant_casos_resuelto ?? 0 }}/{{ count($evaluaciones) }} casos superados)
                    </span>
                </div>

                <div class="card-body p-3">
                    {{-- FILTROS RÁPIDOS DE CASOS --}}
                    @php
                        $totalCasos = $evaluaciones->count();
                        $casosAceptados = $evaluaciones->filter(fn($e) => $e->estado === 'Aceptado')->count();
                        $casosFallados = $evaluaciones->filter(fn($e) => $e->estado !== 'Aceptado' && $e->estado !== 'En Proceso')->count();
                        $casosEnProceso = $evaluaciones->filter(fn($e) => $e->estado === 'En Proceso')->count();
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="btn-group btn-group-sm shadow-none" role="group">
                            <button type="button" class="btn btn-xs btn-dark btn-filtro-casos active" id="btn-filtro-todos" onclick="filtrarCasos('todos')">
                                Todos ({{ $totalCasos }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success btn-filtro-casos" id="btn-filtro-aceptados" onclick="filtrarCasos('aceptados')">
                                <i class="fa fa-check me-1"></i> Aceptados ({{ $casosAceptados }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-filtro-casos" id="btn-filtro-fallados" onclick="filtrarCasos('fallados')">
                                <i class="fa fa-times me-1"></i> Con Error ({{ $casosFallados }})
                            </button>
                        </div>
                    </div>

                    {{-- ALERTA SI HAY CASOS EN PROCESO --}}
                    @if($casosEnProceso > 0)
                        <div class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-center" id="alert_casos_proceso">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            <span class="text-xs">Evaluando {{ $casosEnProceso }} casos de prueba...</span>
                        </div>
                    @endif

                    {{-- ACORDEÓN DE CASOS DE PRUEBA --}}
                    <div class="accordion accordion-flush" id="resultados_accord">
                        @foreach ($evaluaciones as $ev)
                            @php
                                $i = $loop->index;
                                $esEjemplo = (bool)($ev->casos_pruebas->ejemplo ?? false);
                                $estado = $ev->estado ?? 'Pendiente';
                                $esAceptado = ($estado === 'Aceptado');
                                $resultadoLabel = $ev->resultado ?? $estado;
                                $badgeClass = $esAceptado ? 'bg-success' : ($estado === 'En Proceso' ? 'bg-warning text-dark' : 'bg-danger');
                                $borderClass = $esAceptado ? 'border-success' : ($estado === 'En Proceso' ? 'border-warning' : 'border-danger');
                            @endphp

                            <div class="accordion-item mb-2 border rounded caso-evaluacion-item" data-estado="{{ $estado }}">
                                <h2 class="accordion-header" id="heading_{{ $i }}">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#resultados_{{ $i }}" aria-expanded="false"
                                        aria-controls="resultados_{{ $i }}"
                                        id="evaluacion_{{$i}}_button">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-2 flex-wrap gap-1">
                                            <div class="d-flex align-items-center">
                                                <strong class="text-xs text-dark me-2">Caso #{{ $loop->iteration }}</strong>
                                                @if($esEjemplo)
                                                    <span class="badge bg-light text-primary border text-xxs me-2">Ejemplo</span>
                                                @else
                                                    <span class="badge bg-light text-secondary border text-xxs me-2">Evaluación</span>
                                                @endif
                                                <span class="badge {{ $badgeClass }} text-xxs" id="badge_estado_{{$i}}">
                                                    {{ $resultadoLabel }}
                                                </span>
                                            </div>
                                            <div class="text-xxs text-muted" id="informacion_span_{{$i}}">
                                                Tiempo: {{ $ev->tiempo ? $ev->tiempo : '0' }}s &bull; Memoria: {{ $ev->memoria ? $ev->memoria : '0' }} KB
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="resultados_{{ $i }}" class="accordion-collapse collapse"
                                    data-bs-parent="#resultados_accord" aria-labelledby="heading_{{ $i }}">
                                    <div class="accordion-body p-3 bg-light">
                                        <div class="row g-2 mb-2">
                                            {{-- ENTRADAS: Visible si es docente, certamen finalizado, o caso ejemplo --}}
                                            @if(($mostrarCasosCompletos || $esEjemplo) && isset($ev->casos_pruebas->entradas))
                                                <div class="col-12 col-md-4">
                                                    <div class="card h-100 shadow-none border">
                                                        <div class="card-header py-1 px-2 bg-gray-200">
                                                            <span class="text-xxs font-weight-bold text-uppercase text-secondary">Entradas</span>
                                                        </div>
                                                        <div class="card-body p-2 bg-white">
                                                            <pre class="font-monospace text-xs mb-0" id="evaluacion_{{$i}}_e" style="white-space: pre-wrap; font-family: monospace;">{{ $ev->casos_pruebas->entradas }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- SALIDAS ESPERADAS: Visible si es docente, certamen finalizado, o caso ejemplo --}}
                                            @if(($mostrarCasosCompletos || $esEjemplo) && isset($ev->casos_pruebas->salidas))
                                                <div class="col-12 col-md-4">
                                                    <div class="card h-100 shadow-none border">
                                                        <div class="card-header py-1 px-2 bg-gray-200">
                                                            <span class="text-xxs font-weight-bold text-uppercase text-secondary">Salida Esperada</span>
                                                        </div>
                                                        <div class="card-body p-2 bg-white">
                                                            <pre class="font-monospace text-xs mb-0" id="evaluacion_{{$i}}_se" style="white-space: pre-wrap; font-family: monospace;">{{ $ev->casos_pruebas->salidas }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- SALIDA OBTENIDA DEL ESTUDIANTE --}}
                                            <div class="col-12 {{ ($mostrarCasosCompletos || $esEjemplo) ? 'col-md-4' : 'col-12' }}">
                                                <div class="card h-100 shadow-none {{ $borderClass }}" id="card_evaluacion_{{$i}}">
                                                    <div class="card-header py-1 px-2 {{ $esAceptado ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                                        <span class="text-xxs font-weight-bold text-uppercase">Salida Obtenida</span>
                                                    </div>
                                                    <div class="card-body p-2 bg-white">
                                                        <pre class="font-monospace text-xs mb-0" id="evaluacion_{{$i}}_s" style="white-space: pre-wrap; font-family: monospace;">{{ $ev->salida_texto ?: '-' }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ERROR DE COMPILACIÓN O EJECUCIÓN SI EXISTE --}}
                                        <div class="card border-danger shadow-none @if (empty($ev->error_compilacion_texto)) d-none @endif" id="card_error_compilacion_{{$i}}">
                                            <div class="card-header py-1 px-2 bg-danger text-white">
                                                <span class="text-xxs font-weight-bold text-uppercase"><i class="fa fa-exclamation-circle me-1"></i> Mensaje del Compilador / Error</span>
                                            </div>
                                            <div class="card-body p-2 bg-white">
                                                <pre class="text-danger font-monospace text-xs mb-0" id="evaluacion_{{$i}}_ec" style="white-space: pre-wrap; font-family: monospace;">{{ $ev->error_compilacion_texto ?: '-' }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script>
<script src="{{ asset('assets/js/highlightjs/highlight.min.js') }}" type="text/javascript"></script>
<script>
    hljs.highlightAll();

    // Renderizar Fórmulas Matemáticas con KaTeX si está disponible
    if (typeof window.renderFormulas === 'function') {
        window.renderFormulas(document.querySelector('#body_markdown') || document.body);
    }

    // Cálculo y formateo del tiempo de desarrollo
    const tiempo_desarrollo = Math.max(0, {{ (int)$diferencia }});
    const tdHoras = Math.floor(tiempo_desarrollo / 3600);
    const tdMinutos = Math.floor((tiempo_desarrollo % 3600) / 60);
    const tdSegundos = tiempo_desarrollo % 60;
    const string_tiempo_desarrollo = String(tdHoras).padStart(2, '0') + ':' + String(tdMinutos).padStart(2, '0') + ':' + String(tdSegundos).padStart(2, '0');
    const tiempoEl = document.getElementById("tiempo");
    if (tiempoEl) {
        tiempoEl.innerHTML = string_tiempo_desarrollo;
    }

    // Alternar modos de vista (Dividida / Solo Enunciado / Solo Envío)
    function cambiarModoVista(modo) {
        const colEnunciado = document.getElementById('col-enunciado');
        const colEnvio = document.getElementById('col-envio');
        const btnDividida = document.getElementById('btn-vista-dividida');
        const btnEnunciado = document.getElementById('btn-vista-enunciado');
        const btnEnvio = document.getElementById('btn-vista-envio');

        [btnDividida, btnEnunciado, btnEnvio].forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-primary');
        });

        if (modo === 'dividida') {
            colEnunciado.className = 'col-lg-6 col-12 mb-3';
            colEnvio.className = 'col-lg-6 col-12 mb-3';
            btnDividida.classList.add('active', 'btn-primary');
            btnDividida.classList.remove('btn-outline-primary');
        } else if (modo === 'enunciado') {
            colEnunciado.className = 'col-12 mb-3';
            colEnvio.className = 'd-none';
            btnEnunciado.classList.add('active', 'btn-primary');
            btnEnunciado.classList.remove('btn-outline-primary');
        } else if (modo === 'envio') {
            colEnunciado.className = 'd-none';
            colEnvio.className = 'col-12 mb-3';
            btnEnvio.classList.add('active', 'btn-primary');
            btnEnvio.classList.remove('btn-outline-primary');
        }
    }

    // Copiar código del estudiante al portapapeles
    function copiarCodigo() {
        const codigoEl = document.getElementById('codigo_estudiante');
        if (!codigoEl) return;
        const texto = codigoEl.innerText || codigoEl.textContent;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(texto).then(() => {
                mostrarToastCopiado();
            }).catch(() => fallbackCopiar(texto));
        } else {
            fallbackCopiar(texto);
        }
    }

    function fallbackCopiar(texto) {
        const ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            mostrarToastCopiado();
        } catch(e) {}
        document.body.removeChild(ta);
    }

    function mostrarToastCopiado() {
        const btn = document.querySelector('button[onclick="copiarCodigo()"]');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-check text-success me-1"></i> ¡Copiado!';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        }
    }

    // Filtrar casos por estado (todos / aceptados / fallados)
    function filtrarCasos(filtro) {
        const items = document.querySelectorAll('.caso-evaluacion-item');
        items.forEach(el => {
            const estado = el.getAttribute('data-estado');
            if (filtro === 'todos') {
                el.style.display = '';
            } else if (filtro === 'aceptados') {
                el.style.display = (estado === 'Aceptado') ? '' : 'none';
            } else if (filtro === 'fallados') {
                el.style.display = (estado !== 'Aceptado') ? '' : 'none';
            }
        });

        const btnTodos = document.getElementById('btn-filtro-todos');
        const btnAceptados = document.getElementById('btn-filtro-aceptados');
        const btnFallados = document.getElementById('btn-filtro-fallados');
        if (btnTodos) {
            btnTodos.className = 'btn btn-xs btn-filtro-casos ' + (filtro === 'todos' ? 'btn-dark active' : 'btn-outline-dark');
        }
        if (btnAceptados) {
            btnAceptados.className = 'btn btn-xs btn-filtro-casos ' + (filtro === 'aceptados' ? 'btn-success active' : 'btn-outline-success');
        }
        if (btnFallados) {
            btnFallados.className = 'btn btn-xs btn-filtro-casos ' + (filtro === 'fallados' ? 'btn-danger active' : 'btn-outline-danger');
        }
    }

    // Decodificación Base64 segura
    function decodeBase64Safe(str) {
        if (!str) return '';
        try {
            let clean = String(str).trim().replace(/\s+/g, '');
            if (/^[a-zA-Z0-9+/]+={0,2}$/.test(clean) && clean.length % 4 === 0) {
                let decoded = atob(clean);
                if (/[\x20-\x7E\n\r\t]/.test(decoded)) {
                    return decoded;
                }
            }
        } catch (e) {}
        return str;
    }

    // Polling sincrónico si existen casos en proceso
    var evaluaciones_pendientes = {{ (int)$pendientes }};
    const ruta_actualizacion = "{{ route('envio.get_update', ['token' => $envio->token]) }}";

    if (evaluaciones_pendientes > 0) {
        let actualizacion_estado = setInterval(() => {
            if (evaluaciones_pendientes === 0) {
                clearInterval(actualizacion_estado);
            } else {
                fetch(ruta_actualizacion, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(res => res.json())
                .then(result => {
                    var finalizado = true;
                    for (var i = 0; i < result.length; i++) {
                        let button_evaluacion = document.getElementById('evaluacion_' + i + '_button');
                        let texto_salidas = document.getElementById('evaluacion_' + i + '_s');
                        let texto_error_compilacion = document.getElementById('evaluacion_' + i + '_ec');
                        let card_evaluacion = document.getElementById('card_evaluacion_' + i);
                        let badge_estado = document.getElementById('badge_estado_' + i);
                        let card_ec = document.getElementById('card_error_compilacion_' + i);

                        if (result[i]["estado"] === "En Proceso") {
                            finalizado = false;
                        }

                        if (badge_estado) {
                            if (result[i]["estado"] === "Rechazado" || result[i]["estado"] === "Error") {
                                badge_estado.className = 'badge bg-danger text-xxs';
                                if (card_evaluacion) {
                                    card_evaluacion.classList.remove('border-success');
                                    card_evaluacion.classList.add('border-danger');
                                }
                            } else if (result[i]["estado"] === "Aceptado") {
                                badge_estado.className = 'badge bg-success text-xxs';
                                if (card_evaluacion) {
                                    card_evaluacion.classList.remove('border-danger');
                                    card_evaluacion.classList.add('border-success');
                                }
                            }
                            badge_estado.innerHTML = result[i]["resultado"] != null ? result[i]["resultado"] : "En Proceso";
                        }

                        let spanInfo = document.getElementById("informacion_span_" + i);
                        if (spanInfo && result[i]["memoria"] != null && result[i]["tiempo"] != null) {
                            spanInfo.innerHTML = "Tiempo: " + result[i]["tiempo"] + "s &bull; Memoria: " + result[i]["memoria"] + " KB";
                        }

                        if (texto_salidas && result[i]["stout"] != null) {
                            texto_salidas.textContent = decodeBase64Safe(result[i]["stout"]);
                        }
                        if (texto_error_compilacion && result[i]["error_compilacion"] != null) {
                            let decodedEc = decodeBase64Safe(result[i]["error_compilacion"]);
                            texto_error_compilacion.textContent = decodedEc;
                            if (card_ec) {
                                if (decodedEc && decodedEc.trim().length > 0) {
                                    card_ec.classList.remove('d-none');
                                } else {
                                    card_ec.classList.add('d-none');
                                }
                            }
                        }
                    }

                    if (finalizado) {
                        clearInterval(actualizacion_estado);
                        setTimeout(() => { location.reload(); }, 1200);
                    }
                })
                .catch(err => {
                    clearInterval(actualizacion_estado);
                });
            }
        }, 2000);
    }
</script>
@endpush