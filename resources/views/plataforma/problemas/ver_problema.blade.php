@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre, 'breadcrumbs' => [['nombre' => 'Cursos', 'route' => route('cursos.listado')], ['nombre' => 'Problemas', 'route' => route('problemas.listado', ['id' => $id_curso])], ['nombre' => $problema->nombre]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger shadow-sm h-100" style="min-height:40rem">
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
                <div class="card border-danger shadow-sm mb-3" style="min-height:40rem;">
                    <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-grid gap-2 px-1">
                                <a class="btn btn-primary text-nowrap {{ $problema->disponible ? '' : 'disabled' }}"
                                    @if (isset($id_curso)) href="{{ route('problemas.resolver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}" @endif
                                    role="button">{{ $problema->disponible ? 'Resolver Problema' : 'Problema No Disponible' }}</a>
                                <a class="btn btn-outline-secondary btn-sm"
                                    href="{{ route('problemas.pdf_enunciado', ['id_problema' => $problema->id]) }}"
                                    target="_blank" role="button">Descargar PDF Enunciado</a>
                                <a class="btn btn-outline-secondary text-nowrap btn-sm {{ isset($problema->body_editorial) ? '' : 'disabled' }}"
                                    href="{{ route('problemas.ver_editorial', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}"
                                    role="button">{{ isset($problema->body_editorial) ? 'Ver Pistas' : 'Pistas No Disponible' }}</a>
                                @can('ver informe del problema')
                                    <a class="btn btn-outline-secondary text-nowrap btn-sm"
                                        href="{{ route('informe.problema', ['id_curso' => $id_curso, 'id_problema' => $problema->id]) }}"
                                        role="button">Ver Informe del Problema</a>
                                @endcan
                                <a class="btn btn-outline-secondary text-nowrap btn-sm"
                                    href="{{ route('envios.listado', ['id_problema' => $problema->id]) }}" role="button">Ver Mis Envios</a>
                            </div>
                            <hr class="my-3">
                            <h6 class="px-1 font-weight-bold text-dark mb-2" style="font-size: 0.95rem;"><i class="fa fa-info-circle me-1 text-primary"></i> Información:</h6>
                            <ul class="list-group list-group-flush border rounded shadow-none" style="font-size: 0.875rem;">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Puntos:</strong>
                                    <span class="badge bg-primary rounded-pill" style="font-size: 0.8rem;">{{ $problema->casos_de_prueba()->sum('puntos') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Límite Tiempo:</strong>
                                    <span>{{ $problema->tiempo_limite ? $problema->tiempo_limite . ' s' : 'No definido' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Límite Memoria:</strong>
                                    <span>{{ $problema->memoria_limite ? $problema->memoria_limite . ' KB' : 'No definido' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Curso(s):</strong>
                                    <span class="text-secondary text-end ms-2">{{ implode(', ', $problema->cursos()->where('cursos.id', '=', $id_curso)->pluck('nombre')->toArray()) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Estado:</strong>
                                    <span class="badge {{ $problema->estado ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.8rem;">{{ $problema->estado ? 'Resuelto' : 'No Resuelto' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Categorías:</strong>
                                    <span class="text-secondary text-end ms-2">{{ implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray()) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <strong>Lenguajes:</strong>
                                    <div class="d-flex flex-wrap gap-1 justify-content-end ms-2">
                                        @foreach($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $item) 
                                            <span class="badge bg-secondary" style="font-size: 0.75rem;">{{strtoupper($item)}}</span>
                                        @endforeach
                                    </div>
                                </li>
                                @if (isset($problema->fecha_inicio))
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <strong>Fecha Inicio:</strong>
                                        <span class="text-secondary">{{ $problema->fecha_inicio }}</span>
                                    </li>
                                @endif
                                @if (isset($problema->fecha_termino))
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <strong>Fecha Término:</strong>
                                        <span class="text-secondary">{{ $problema->fecha_termino }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = document.querySelectorAll("#body_markdown table");
            if (table != null) {
                for(var i = 0; i < table.length; i++){
                    var table_body = table[i].querySelector("tbody");
                    if (table_body) {
                        table[i].classList.add("table", "table-bordered", "table-hover", "mt-3");
                        table[i].style.width = "auto";
                        table_body.classList.add("table-group-divider");
                    }
                }
            }
            if (typeof window.renderFormulas === 'function') {
                window.renderFormulas(document.querySelector('#body_markdown') || document.body);
            }
        });
    </script>
@endpush

