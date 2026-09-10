@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre, 'breadcrumbs' => [['nombre' => 'Cursos', 'route' => route('cursos.listado')], ['nombre' => 'Problemas', 'route' => route('problemas.listado', ['id' => $id_curso])], ['nombre' => $problema->nombre]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger overflow-auto" style="height:40rem">
                    <div class="card-header">
                        Enunciado
                    </div>
                    <div class="card-body p-4 text-wrap" id="body_markdown">
                        {!! Str::markdown($problema->body_problema, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}

                        @php
                            $casos_ejemplo = $problema->casos_de_prueba()->where('ejemplo', true)->get();
                        @endphp

                        @if($casos_ejemplo->count() > 0)
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-vials me-2"></i>Ejemplos de Entrada y Salida</h6>
                                <div class="row">
                                    @foreach($casos_ejemplo as $caso)
                                        <div class="col-12 mb-3">
                                            <div class="card bg-gray-100 border shadow-none">
                                                <div class="card-header py-1 px-3 bg-gray-200">
                                                    <span class="text-xs font-weight-bold text-dark">Ejemplo #{{ $loop->iteration }}</span>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="row">
                                                        @if(!is_null($caso->entradas) && trim($caso->entradas) !== '')
                                                            <div class="col-md-6 mb-2 mb-md-0">
                                                                <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Entrada</span>
                                                                <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->entradas }}</pre>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                                <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
                                                            </div>
                                                        @else
                                                            <div class="col-12">
                                                                <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                                <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
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
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger" style="height:40rem">
                    <div class="card-body px-3">
                        <div class="row px-5">
                            <a class="btn btn-primary text-nowrap btn-block {{ $problema->disponible ? '' : 'disabled' }}"
                                @if (isset($id_curso)) href="{{ route('problemas.resolver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}" @endif
                                role="button">{{ $problema->disponible ? 'Resolver Problema' : 'Problema No Disponible' }}</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary btn-sm btn-block"
                                href="{{ route('problemas.pdf_enunciado', ['id_problema' => $problema->id]) }}"
                                target="_blank" role="button">Descargar PDF del Enunciado</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block {{ isset($problema->body_editorial) ? '' : 'disabled' }}"
                                href="{{ route('problemas.ver_editorial', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}"
                                role="button">{{ isset($problema->body_editorial) ? 'Ver Pistas' : 'Pistas No Disponible' }}</a>
                        </div>
                        @can('ver informe del problema')
                            <div class="row px-5 mt-2 mb-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                    href="{{ route('informe.problema', ['id_curso' => $id_curso, 'id_problema' => $problema->id]) }}"
                                    role="button">Ver Informe del Problema</a>
                            </div>
                        @endcan
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                href="{{ route('envios.listado', ['id_problema' => $problema->id]) }}" role="button">Ver Mis
                                Envios</a>
                        </div>
                        <hr>
                        <h6 class="ms-3 mt-3"><strong>Información:</strong></h6>
                        <ul class="list-group mt-3">
                            <li class="list-group-item"><strong>Puntos:</strong>
                                {{ $problema->casos_de_prueba()->sum('puntos') }}</li>
                            <li class="list-group-item"><strong>Límite de Tiempo:</strong>
                                {{ $problema->tiempo_limite ? $problema->tiempo_limite . ' s' : 'No definido' }}</li>
                            <li class="list-group-item"><strong>Límite de Memoria:</strong>
                                {{ $problema->memoria_limite ? $problema->memoria_limite . ' KB' : 'No definido' }}</li>
                            <li class="list-group-item"><strong>Curso(s):</strong>
                                {{ implode(', ', $problema->cursos()->where('cursos.id', '=', $id_curso)->pluck('nombre')->toArray()) }}
                            </li>
                            <li class="list-group-item"><strong>Estado:</strong> <span
                                    class="badge {{ $problema->estado ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $problema->estado ? 'Resuelto' : 'No Resuelto' }}</span>
                            </li>
                            <li class="list-group-item"><strong>Categorías:</strong>
                                {{ implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray()) }}</li>
                            <li class="list-group-item"><strong>Lenguajes:</strong>
                                @foreach($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $item) 
                                <span class="badge text-bg-secondary">{{strtoupper($item)}}</span>
                                @endforeach
                            </li>
                            @if (isset($problema->fecha_inicio))
                                <li class="list-group-item"><strong>Fecha de Inicio:</strong> {{ $problema->fecha_inicio }}
                                </li>
                            @endif
                            @if (isset($problema->fecha_termino))
                                <li class="list-group-item"><strong>Fecha de Termino:</strong>
                                    {{ $problema->fecha_termino }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var table = document.querySelectorAll("#body_markdown table")
        if (table != null) {
            for(var i = 0; i<table.length; i++){
                var table_body = table[i].querySelector("tbody")
                table[i].classList.add("table")
                table[i].classList.add("table-bordered")
                table[i].classList.add("table-hover")
                table[i].classList.add("mt-3")
                table[i].style.width = "auto"
                table_body.classList.add("table-group-divider")
            }
            
        }
    </script>
@endpush
