@php
    $certamenObj = (isset($res_certamen) && $res_certamen->certamen) ? $res_certamen->certamen : null;
    $totalCasos = $evaluaciones->count();
    $totalCasosResumen = max(
        $totalCasos,
        isset($problema) && isset($problema->casos_de_prueba) ? $problema->casos_de_prueba->count() : 0,
        (int) ($envio->cant_casos_resuelto ?? 0)
    );
    $casosAceptados = $evaluaciones->filter(fn($e) => $e->estado === 'Aceptado')->count();
    $casosFallados = $evaluaciones->filter(fn($e) => $e->estado !== 'Aceptado' && $e->estado !== 'En Proceso')->count();
    $casosEnProceso = $evaluaciones->filter(fn($e) => $e->estado === 'En Proceso')->count();
    $tuvoVariacionFormato = $evaluaciones->contains('resultado', 'Aceptado (Variación de Formato)');
@endphp

@extends('layouts.app', [
    'class' => 'g-sidenav-show bg-gray-100',
    'title_url' => 'Detalle Envío #' . $envio->id
])

@push('css')
<style>
    #row-principal > [class*="col-"] {
        min-width: 0;
    }
    #col-enunciado .card,
    #col-envio .card {
        min-width: 0;
    }
    #body_markdown img,
    .markdown-content img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin: 1rem auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    #body_markdown blockquote {
        border-left: 4px solid #cbd5e1;
        background-color: #f8fafc;
        padding: 0.65rem 1rem;
        margin: 1rem 0;
        border-radius: 0 0.375rem 0.375rem 0;
        color: #475569;
    }
    #body_markdown blockquote p:last-child {
        margin-bottom: 0;
    }
    #body_markdown pre,
    .markdown-content pre {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        margin: 0.85rem 0;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.83rem;
        max-width: 100%;
    }
    #body_markdown pre code {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        color: inherit;
    }
    #body_markdown :not(pre) > code {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 0.25rem;
        padding: 0.12rem 0.35rem;
        font-size: 0.85em;
        color: #b91c1c;
    }
    #body_markdown table,
    .markdown-content table {
        width: 100%;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-collapse: collapse;
    }
    #body_markdown th,
    #body_markdown td,
    .markdown-content th,
    .markdown-content td {
        padding: 0.5rem 0.75rem;
        border: 1px solid #dee2e6;
    }
    #body_markdown thead th,
    .markdown-content th {
        background-color: #f8fafc;
        font-weight: 600;
    }
    #body_markdown hr {
        margin: 1.25rem 0;
        border-top: 1px solid #cbd5e1;
        opacity: 0.6;
    }
    .split-scroll-body {
        max-height: 75vh;
        overflow-y: auto;
    }
    #body_markdown h1,
    #body_markdown h2,
    #body_markdown h3 {
        font-size: 1.15rem;
        margin-top: 0.85rem;
        margin-bottom: 0.5rem;
    }
    #body_markdown h4,
    #body_markdown h5 {
        font-size: 1rem;
        margin-top: 0.75rem;
        margin-bottom: 0.4rem;
    }
    .code-scroll-box,
    .code-scroll-box code,
    .code-scroll-box code[class*="language-"],
    #body_markdown pre code[class*="language-"] {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        font-size: 0.85rem;
        line-height: 1.55;
        text-shadow: none !important;
    }
    .code-scroll-box {
        max-height: 360px;
        overflow: auto;
        background: #282c34 !important;
        color: #abb2bf !important;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
    .code-scroll-box code.hljs,
    .code-scroll-box code[class*="language-"] {
        background: #282c34 !important;
        color: #abb2bf;
        padding: 0 !important;
    }
    html[data-theme="dark"] #body_markdown blockquote {
        background-color: #161B22;
        border-left-color: #334155;
        color: #CBD5E1;
    }
    html[data-theme="dark"] #body_markdown pre,
    html[data-theme="dark"] .markdown-content pre {
        background-color: #0F172A;
        border-color: #262F3F;
        color: #E2E8F0;
    }
    html[data-theme="dark"] #body_markdown :not(pre) > code {
        background-color: #1E293B;
        border-color: #334155;
        color: #FCA5A5;
    }
</style>
<link rel="stylesheet" href="{{ asset('assets/js/highlightjs/styles/atom-one-dark.min.css') }}">
@endpush

@section('content')
@include('layouts.navbars.auth.topnav', [
    'title' => 'Detalle de Envío #' . $envio->id
])

<div class="container-fluid py-3 px-2 px-md-4">
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-white mb-3" role="alert">
            <span class="text-sm"><i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}</span>
            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-white mb-3" role="alert">
            <span class="text-sm"><i class="fa fa-check-circle me-1"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    {{-- TARJETA SUPERIOR: NAVEGACIÓN, TÍTULO DEL PROBLEMA, ESTADO Y MÉTRICAS --}}
    <div class="card shadow-xs border mb-4">
        <div class="card-header pb-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($certamenObj)
                    <a href="{{ route('informe.certamen', ['id_certamen' => $certamenObj->id]) }}" class="btn btn-sm btn-outline-secondary mb-0">
                        <i class="fa fa-arrow-left me-1"></i> Informe Evaluación
                    </a>
                    @if($res_certamen)
                        <a href="{{ route('informe.certamen.detalle', ['id_certamen' => $certamenObj->id, 'id_res_certamen' => $res_certamen->id]) }}" class="btn btn-sm btn-outline-primary mb-0">
                            <i class="fa fa-list me-1"></i> Envíos del Estudiante
                        </a>
                    @endif
                @elseif(isset($envio->curso))
                    <a href="{{ route('informe.envios.problema', ['id_curso' => $envio->curso->id, 'id_problema' => $problema->id]) }}" class="btn btn-sm btn-outline-secondary mb-0">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Informe de Envíos
                    </a>
                @endif
            </div>

            <div class="btn-group shadow-none" role="group" aria-label="Modo de visualización">
                <button type="button" class="btn btn-sm btn-primary active mb-0" id="btn-vista-dividida" onclick="cambiarModoVista('dividida')">
                    <i class="fa fa-columns me-1"></i> Vista Dividida
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="btn-vista-enunciado" onclick="cambiarModoVista('enunciado')">
                    <i class="fa fa-file-text-o me-1"></i> Solo Enunciado
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="btn-vista-envio" onclick="cambiarModoVista('envio')">
                    <i class="fa fa-code me-1"></i> Solo Envío
                </button>
            </div>
        </div>

        <div class="card-body pt-3 pb-3">
            {{-- Título del Problema y Veredicto General --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-gradient-dark font-monospace">Envío #{{ $envio->id }}</span>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ $problema->nombre }}</h5>
                        <span class="badge border border-secondary text-secondary font-monospace text-xxs">{{ $problema->codigo }}</span>
                    </div>
                    <p class="text-xs text-secondary mb-0 mt-1">
                        @if($certamenObj)
                            <span class="me-2 text-danger font-weight-bold"><i class="fa fa-award me-1"></i>{{ $certamenObj->nombre }}</span> &bull;
                        @endif
                        <span class="me-2"><i class="fa fa-graduation-cap me-1"></i>{{ $envio->curso->nombre ?? 'Sin curso' }}</span> &bull;
                        <span><i class="fa fa-calendar me-1"></i>{{ isset($envio->created_at) ? \Carbon\Carbon::parse($envio->created_at)->format('d/m/Y H:i:s') : '-' }}</span>
                    </p>
                </div>

                <div>
                    @if($envio->solucionado)
                        <span class="badge badge-lg bg-gradient-success">
                            <i class="fa fa-check-circle me-1"></i> {{ $tuvoVariacionFormato ? 'ACEPTADO (VARIACIÓN DE FORMATO)' : 'ACEPTADO' }} ({{ $envio->puntaje ?? 0 }} pts)
                        </span>
                    @elseif($casosEnProceso > 0)
                        <span class="badge badge-lg bg-gradient-warning text-dark">
                            <i class="fa fa-spinner fa-spin me-1"></i> EN PROCESO
                        </span>
                    @else
                        <span class="badge badge-lg bg-gradient-danger">
                            <i class="fa fa-times-circle me-1"></i> {{ \App\Support\Judge0StatusHelper::traducir($envio->resultado ?? 'Rechazado') }} ({{ $envio->puntaje ?? 0 }} pts)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Tira de 4 Métricas Clave --}}
            <div class="row g-2">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded bg-gray-100 border h-100">
                        <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fa fa-user text-white text-xs"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xxs text-uppercase text-secondary font-weight-bold d-block">Estudiante</span>
                            <h6 class="text-xs font-weight-bold text-dark text-truncate mb-0">{{ $envio->usuario->firstname }} {{ $envio->usuario->lastname }}</h6>
                            <span class="text-xxs text-secondary font-monospace">RUT: {{ $envio->usuario->rut ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded bg-gray-100 border h-100">
                        <div class="icon icon-shape icon-sm bg-gradient-info shadow text-center border-radius-md d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fa fa-code text-white text-xs"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xxs text-uppercase text-secondary font-weight-bold d-block">Lenguaje &amp; IP</span>
                            <h6 class="text-xs font-weight-bold text-dark text-truncate mb-0">{{ $envio->lenguaje->nombre ?? 'Lenguaje' }}</h6>
                            <span class="text-xxs text-secondary font-monospace">IP: {{ $envio->ip_origen ?? '127.0.0.1' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded bg-gray-100 border h-100">
                        <div class="icon icon-shape icon-sm bg-gradient-success shadow text-center border-radius-md d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fa fa-check-square text-white text-xs"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xxs text-uppercase text-secondary font-weight-bold d-block">Casos Evaluados</span>
                            <h6 class="text-xs font-weight-bold text-dark mb-0">
                                @if($totalCasosResumen > 0)
                                    {{ $envio->cant_casos_resuelto ?? 0 }} / {{ $totalCasosResumen }} superados
                                @else
                                    {{ $envio->cant_casos_resuelto ?? 0 }} superado(s)
                                @endif
                            </h6>
                            <span class="text-xxs text-secondary">Puntaje: {{ $envio->puntaje ?? 0 }} pts</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded bg-gray-100 border h-100">
                        <div class="icon icon-shape icon-sm bg-gradient-warning shadow text-center border-radius-md d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fa fa-clock-o text-white text-xs"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xxs text-uppercase text-secondary font-weight-bold d-block">Tiempo Desarrollo</span>
                            <h6 class="text-xs font-weight-bold text-dark font-monospace mb-0" id="tiempo">00:00:00</h6>
                            <span class="text-xxs text-secondary">Duración del intento</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL: ENUNCIADO + ENVÍO DEL ESTUDIANTE --}}
    <div class="row g-3 align-items-start" id="row-principal">

        {{-- COLUMNA IZQUIERDA: ENUNCIADO DEL PROBLEMA --}}
        <div id="col-enunciado" class="col-12 col-xl-6">
            <div class="card shadow-xs border">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-dark text-sm">
                        <i class="fa fa-book text-danger me-2"></i>Enunciado del Problema
                    </h6>
                    <div class="d-flex align-items-center gap-1">
                        @if(isset($problema->dificultad))
                            @php
                                $difClass = 'bg-gradient-success';
                                $dif = strtolower($problema->dificultad);
                                if(str_contains($dif, 'medio') || str_contains($dif, 'intermedio')) $difClass = 'bg-gradient-warning text-dark';
                                elseif(str_contains($dif, 'dificil') || str_contains($dif, 'avanzado')) $difClass = 'bg-gradient-danger text-white';
                            @endphp
                            <span class="badge {{ $difClass }} text-uppercase text-xxs">{{ $problema->dificultad }}</span>
                        @endif
                        @if(isset($problema->categoria))
                            <span class="badge border border-secondary text-dark text-xxs">{{ $problema->categoria->nombre ?? '' }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-3 p-md-4 text-wrap split-scroll-body" id="body_markdown">
                    @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
                        <div class="alert alert-warning text-dark border-warning d-flex align-items-start gap-2 py-2 px-3 mb-3 text-xs" role="alert">
                            <i class="fa fa-exclamation-triangle text-dark mt-1"></i>
                            <div>
                                <strong>Restricción del Problema:</strong> {{ $problema->restricciones }}
                            </div>
                        </div>
                    @endif

                    <div class="markdown-content text-dark text-sm" style="line-height: 1.65;">
                        {!! \App\Support\MarkdownRenderer::render($problema->body_problema ?? '') !!}
                    </div>

                    @php
                        $casos_ejemplo = $problema->casos_de_prueba()->where('ejemplo', true)->orderBy('id', 'asc')->get();
                        if ($casos_ejemplo->isEmpty() && $problema->casos_de_prueba()->count() > 0) {
                            $casos_ejemplo = $problema->casos_de_prueba()->orderBy('id', 'asc')->take(1)->get();
                        }
                    @endphp

                    @if($casos_ejemplo->count() > 0)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="font-weight-bold text-primary text-sm mb-3">
                                <i class="fa fa-vials me-2"></i>Ejemplos de Entrada y Salida
                            </h6>
                            <div class="row g-2">
                                @foreach($casos_ejemplo as $caso)
                                    <div class="col-12">
                                        <div class="card bg-gray-100 border shadow-none">
                                            <div class="card-header py-1 px-3 bg-gray-200 d-flex justify-content-between align-items-center">
                                                <span class="text-xs font-weight-bold text-dark">Ejemplo #{{ $loop->iteration }}</span>
                                                @if(isset($caso->puntos))
                                                    <span class="text-xxs text-secondary">{{ $caso->puntos }} pts</span>
                                                @endif
                                            </div>
                                            <div class="card-body p-2 p-md-3">
                                                <div class="row g-2">
                                                    @if(!is_null($caso->entradas) && trim($caso->entradas) !== '')
                                                        <div class="col-sm-6 col-12">
                                                            <span class="text-xxs font-weight-bold text-uppercase d-block mb-1 text-secondary">Entrada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.8rem;">{{ $caso->entradas }}</pre>
                                                        </div>
                                                        <div class="col-sm-6 col-12">
                                                            <span class="text-xxs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.8rem;">{{ $caso->salidas }}</pre>
                                                        </div>
                                                    @else
                                                        <div class="col-12">
                                                            <span class="text-xxs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                            <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.8rem;">{{ $caso->salidas }}</pre>
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

        {{-- COLUMNA DERECHA: ENVÍO DEL ESTUDIANTE --}}
        <div id="col-envio" class="col-12 col-xl-6">

            {{-- TARJETA 1: CÓDIGO FUENTE ENTREGADO --}}
            <div class="card shadow-xs border mb-3" id="card-codigo-fuente">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-dark text-sm">
                        <i class="fa fa-code text-primary me-2"></i>Código Fuente Entregado
                        <span class="badge border border-secondary text-dark ms-1 text-xxs">{{ $envio->lenguaje->nombre ?? 'Código' }}</span>
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="copiarCodigo()">
                        <i class="fa fa-copy me-1"></i> Copiar
                    </button>
                </div>
                <div class="card-body p-0">
                    <pre class="mb-0 px-3 pt-3 pb-3 code-scroll-box"><code class="language-{{ $highlightjs_choice }} p-0 bg-transparent d-block" id="codigo_estudiante">{{ $envio->codigo }}</code></pre>
                </div>
            </div>

            {{-- TARJETA 2: VERIFICACIÓN DE RESTRICCIONES DEL BOT --}}
            @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
                <div class="card shadow-xs border border-info mb-3">
                    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2 px-3 flex-wrap gap-2">
                        <h6 class="mb-0 text-dark font-weight-bold text-xs">
                            <i class="fa fa-robot text-info me-2"></i>Verificación de Restricciones del Bot
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            @if(!is_null($envio->cumple_restricciones))
                                @if($envio->cumple_restricciones)
                                    <span class="badge bg-gradient-success text-xxs"><i class="fa fa-check-circle me-1"></i> CUMPLE</span>
                                @else
                                    <span class="badge bg-gradient-danger text-xxs"><i class="fa fa-times-circle me-1"></i> NO CUMPLE</span>
                                @endif
                            @else
                                <span class="badge bg-gradient-warning text-dark text-xxs"><i class="fa fa-clock-o me-1"></i> Pendiente</span>
                            @endif
                            <a href="{{ route('envios.verificar_restricciones', ['token' => $envio->token, 'from' => 'detalle']) }}" onclick="this.classList.add('disabled'); this.setAttribute('aria-disabled','true'); this.innerHTML='<i class=\'fa fa-spinner fa-spin me-1\'></i> Verificando...';" class="btn btn-xs btn-outline-info mb-0">
                                <i class="fa fa-refresh me-1"></i> Re-evaluar
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @if(isset($envio->verificacion_restricciones))
                            <div class="p-2 rounded bg-gray-100 border text-dark text-xs" style="line-height: 1.5; white-space: pre-wrap;">{{ trim($envio->verificacion_restricciones) }}</div>
                        @else
                            <p class="text-secondary text-xs mb-0">Aún no se ha verificado si el código cumple con las restricciones.</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- TARJETA 3: EVALUACIÓN DE CASOS DE PRUEBA --}}
            <div class="card shadow-xs border mb-3">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 font-weight-bold text-dark text-sm">
                        <i class="fa fa-vials text-success me-2"></i>Casos de Prueba
                        @if($totalCasosResumen > 0)
                            ({{ $envio->cant_casos_resuelto ?? 0 }}/{{ $totalCasosResumen }})
                        @endif
                    </h6>
                    @if($totalCasos > 0)
                        <div class="btn-group shadow-none" role="group">
                            <button type="button" class="btn btn-xs btn-dark btn-filtro-casos active mb-0" id="btn-filtro-todos" onclick="filtrarCasos('todos')">
                                Todos ({{ $totalCasos }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success btn-filtro-casos mb-0" id="btn-filtro-aceptados" onclick="filtrarCasos('aceptados')">
                                Aceptados ({{ $casosAceptados }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-filtro-casos mb-0" id="btn-filtro-fallados" onclick="filtrarCasos('fallados')">
                                Con Error ({{ $casosFallados }})
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-body p-3">
                    @if($totalCasos === 0)
                        <p class="text-xs text-secondary mb-0">
                            <i class="fa fa-info-circle me-1"></i> No hay detalle por caso registrado para este envío (resultado global: {{ $envio->cant_casos_resuelto ?? 0 }} caso(s) superados, {{ $envio->puntaje ?? 0 }} pts).
                        </p>
                    @endif
                    @if($casosEnProceso > 0)
                        <div class="alert alert-warning text-dark py-2 px-3 mb-3 d-flex align-items-center" id="alert_casos_proceso">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            <span class="text-xs">Evaluando {{ $casosEnProceso }} casos de prueba...</span>
                        </div>
                    @endif

                    <div class="accordion accordion-flush" id="resultados_accord">
                        @foreach ($evaluaciones as $ev)
                            @php
                                $i = $loop->index;
                                $esEjemplo = (bool)($ev->casos_pruebas->ejemplo ?? false);
                                $estado = $ev->estado ?? 'Pendiente';
                                $esAceptado = ($estado === 'Aceptado');
                                $resultadoLabel = \App\Support\Judge0StatusHelper::traducir($ev->resultado ?? $estado);
                                $badgeClass = $esAceptado ? 'bg-gradient-success' : ($estado === 'En Proceso' ? 'bg-gradient-warning text-dark' : 'bg-gradient-danger');
                                $borderClass = $esAceptado ? 'border-success' : ($estado === 'En Proceso' ? 'border-warning' : 'border-danger');
                            @endphp

                            <div class="accordion-item mb-2 border rounded caso-evaluacion-item" data-estado="{{ $estado }}">
                                <h2 class="accordion-header" id="heading_{{ $i }}">
                                    <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#resultados_{{ $i }}" aria-expanded="false"
                                        aria-controls="resultados_{{ $i }}"
                                        id="evaluacion_{{$i}}_button">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-2 flex-wrap gap-1">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <strong class="text-xs text-dark me-1">Caso #{{ $loop->iteration }}</strong>
                                                <span class="badge border text-xxs {{ $esEjemplo ? 'border-primary text-primary' : 'border-secondary text-secondary' }}">
                                                    {{ $esEjemplo ? 'Ejemplo' : 'Evaluación' }}
                                                </span>
                                                <span class="badge {{ $badgeClass }} text-xxs" id="badge_estado_{{$i}}">
                                                    {{ $resultadoLabel }}
                                                </span>
                                            </div>
                                            <span class="text-xxs text-secondary font-monospace" id="informacion_span_{{$i}}">
                                                {{ $ev->tiempo ?: '0' }}s &bull; {{ $ev->memoria ?: '0' }} KB
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="resultados_{{ $i }}" class="accordion-collapse collapse"
                                    data-bs-parent="#resultados_accord" aria-labelledby="heading_{{ $i }}">
                                    <div class="accordion-body p-2 p-md-3 bg-gray-100">
                                        <div class="row g-2 mb-2">
                                            @if(isset($ev->casos_pruebas->entradas))
                                                <div class="col-12 col-sm-6">
                                                    <div class="card h-100 shadow-none border">
                                                        <div class="card-header py-1 px-2 bg-gray-200">
                                                            <span class="text-xxs font-weight-bold text-uppercase text-secondary">Entradas</span>
                                                        </div>
                                                        <div class="card-body p-2 bg-white">
                                                            <pre class="font-monospace text-xs mb-0 text-dark" id="evaluacion_{{$i}}_e" style="white-space: pre-wrap;">{{ $ev->casos_pruebas->entradas }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($ev->casos_pruebas->salidas))
                                                <div class="col-12 col-sm-6">
                                                    <div class="card h-100 shadow-none border">
                                                        <div class="card-header py-1 px-2 bg-gray-200">
                                                            <span class="text-xxs font-weight-bold text-uppercase text-secondary">Salida Esperada</span>
                                                        </div>
                                                        <div class="card-body p-2 bg-white">
                                                            <pre class="font-monospace text-xs mb-0 text-dark" id="evaluacion_{{$i}}_se" style="white-space: pre-wrap;">{{ $ev->casos_pruebas->salidas }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="col-12">
                                                <div class="card h-100 shadow-none {{ $borderClass }}" id="card_evaluacion_{{$i}}">
                                                    <div class="card-header py-1 px-2 {{ $esAceptado ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                                        <span class="text-xxs font-weight-bold text-uppercase">Salida Obtenida</span>
                                                    </div>
                                                    <div class="card-body p-2 bg-white">
                                                        <pre class="font-monospace text-xs mb-0 text-dark" id="evaluacion_{{$i}}_s" style="white-space: pre-wrap;">{{ $ev->salida_texto ?: '-' }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card border-danger shadow-none @if (empty($ev->error_compilacion_texto)) d-none @endif" id="card_error_compilacion_{{$i}}">
                                            <div class="card-header py-1 px-2 bg-danger text-white">
                                                <span class="text-xxs font-weight-bold text-uppercase"><i class="fa fa-exclamation-circle me-1"></i> Mensaje del Compilador / Error</span>
                                            </div>
                                            <div class="card-body p-2 bg-white">
                                                <pre class="text-danger font-monospace text-xs mb-0" id="evaluacion_{{$i}}_ec" style="white-space: pre-wrap;">{{ $ev->error_compilacion_texto ?: '-' }}</pre>
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
<script src="{{ asset('assets/js/highlightjs/highlight.min.js') }}" type="text/javascript"></script>
<script>
    hljs.highlightAll();

    document.addEventListener('DOMContentLoaded', function() {
        var container = document.querySelector('#body_markdown');
        var tables = document.querySelectorAll('#body_markdown table');
        if (tables != null) {
            for (var i = 0; i < tables.length; i++) {
                var table_body = tables[i].querySelector('tbody');
                if (table_body) {
                    tables[i].classList.add('table', 'table-bordered', 'table-hover', 'mt-3');
                    tables[i].style.width = 'auto';
                    table_body.classList.add('table-group-divider');
                }
            }
        }
        if (container) {
            container.querySelectorAll('pre > code.language-katex, pre > code.language-math, pre > code.language-latex').forEach(function(codeEl) {
                var pre = codeEl.parentElement;
                var div = document.createElement('div');
                div.className = 'katex-block my-3';
                div.textContent = '$$' + (codeEl.textContent || '').trim() + '$$';
                pre.replaceWith(div);
            });
        }
        if (typeof window.renderFormulas === 'function') {
            window.renderFormulas(container || document.body);
        }
    });

    const tiempo_desarrollo = Math.max(0, {{ (int)$diferencia }});
    const tdHoras = Math.floor(tiempo_desarrollo / 3600);
    const tdMinutos = Math.floor((tiempo_desarrollo % 3600) / 60);
    const tdSegundos = tiempo_desarrollo % 60;
    const string_tiempo_desarrollo = String(tdHoras).padStart(2, '0') + ':' + String(tdMinutos).padStart(2, '0') + ':' + String(tdSegundos).padStart(2, '0');
    const tiempoEl = document.getElementById("tiempo");
    if (tiempoEl) {
        tiempoEl.innerHTML = string_tiempo_desarrollo;
    }

    function cambiarModoVista(modo) {
        const colEnunciado = document.getElementById('col-enunciado');
        const colEnvio = document.getElementById('col-envio');
        const bodyMarkdown = document.getElementById('body_markdown');
        const btnDividida = document.getElementById('btn-vista-dividida');
        const btnEnunciado = document.getElementById('btn-vista-enunciado');
        const btnEnvio = document.getElementById('btn-vista-envio');

        [btnDividida, btnEnunciado, btnEnvio].forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-primary');
        });

        if (modo === 'dividida') {
            colEnunciado.className = 'col-12 col-xl-6';
            colEnvio.className = 'col-12 col-xl-6';
            if (bodyMarkdown) bodyMarkdown.classList.add('split-scroll-body');
            btnDividida.classList.add('active', 'btn-primary');
            btnDividida.classList.remove('btn-outline-primary');
        } else if (modo === 'enunciado') {
            colEnunciado.className = 'col-12';
            colEnvio.className = 'd-none';
            if (bodyMarkdown) bodyMarkdown.classList.remove('split-scroll-body');
            btnEnunciado.classList.add('active', 'btn-primary');
            btnEnunciado.classList.remove('btn-outline-primary');
        } else if (modo === 'envio') {
            colEnunciado.className = 'd-none';
            colEnvio.className = 'col-12';
            btnEnvio.classList.add('active', 'btn-primary');
            btnEnvio.classList.remove('btn-outline-primary');
        }
    }

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
            btnTodos.className = 'btn btn-xs btn-filtro-casos mb-0 ' + (filtro === 'todos' ? 'btn-dark active' : 'btn-outline-dark');
        }
        if (btnAceptados) {
            btnAceptados.className = 'btn btn-xs btn-filtro-casos mb-0 ' + (filtro === 'aceptados' ? 'btn-success active' : 'btn-outline-success');
        }
        if (btnFallados) {
            btnFallados.className = 'btn btn-xs btn-filtro-casos mb-0 ' + (filtro === 'fallados' ? 'btn-danger active' : 'btn-outline-danger');
        }
    }

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
                                badge_estado.className = 'badge bg-gradient-danger text-xxs';
                                if (card_evaluacion) {
                                    card_evaluacion.classList.remove('border-success');
                                    card_evaluacion.classList.add('border-danger');
                                }
                            } else if (result[i]["estado"] === "Aceptado") {
                                badge_estado.className = 'badge bg-gradient-success text-xxs';
                                if (card_evaluacion) {
                                    card_evaluacion.classList.remove('border-danger');
                                    card_evaluacion.classList.add('border-success');
                                }
                            }
                            badge_estado.innerHTML = result[i]["resultado"] != null ? result[i]["resultado"] : "En Proceso";
                        }

                        let spanInfo = document.getElementById("informacion_span_" + i);
                        if (spanInfo && result[i]["memoria"] != null && result[i]["tiempo"] != null) {
                            spanInfo.innerHTML = result[i]["tiempo"] + "s &bull; " + result[i]["memoria"] + " KB";
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
