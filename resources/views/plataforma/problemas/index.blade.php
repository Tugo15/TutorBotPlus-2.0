@extends('layout_plataforma.app', ['title_html' => 'Problemas', 'title' => $curso->nombre, 'breadcrumbs' => [["nombre" => "Cursos", "route" => route("cursos.listado")], ["nombre" => $curso->nombre]]])

@section('content')
    <div class="container-fluid px-3 px-md-4">
        @include('components.alert')
        
        <div class="card border-danger">
            <div class="card-body px-3 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('cursos.listado') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center mb-0" title="Volver a Cursos">
                            <i class="fa fa-arrow-left me-1"></i> Cursos
                        </a>
                        <h5 class="mb-0"><strong>{{ $curso->nombre }}</strong></h5>
                        @if(!empty($curso->codigo))
                            <span class="badge text-bg-light border fw-semibold">{{ $curso->codigo }}</span>
                        @endif
                    </div>
                    <div class="btn-group flex-wrap shadow-sm" role="group" aria-label="Selección de vista">
                        <button type="button" class="btn btn-danger btn-md px-3 px-md-4 font-weight-bold d-inline-flex align-items-center active" id="btn-problemas" onclick="mostrarSeccion('problemas')">
                            <i class="fa fa-code me-1"></i> Problemas de Práctica ({{ $problemas->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-md px-3 px-md-4 font-weight-bold d-inline-flex align-items-center" id="btn-evaluaciones" onclick="mostrarSeccion('evaluaciones')">
                            <i class="fa fa-file-text me-1"></i> Evaluaciones del Curso ({{ $evaluaciones->count() }})
                        </button>
                    </div>
                </div>
                <hr class="mt-2 mb-4">

                <!-- VISTA 1 PRINCIPAL: PROBLEMAS DE PRÁCTICA DEL CURSO -->
                <div id="seccion-problemas">
                    <div class="table-responsive">
                        <table id="table" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-center">Puntos</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Estado</th>
                                    <th>Creado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($problemas as $problema)
                                    <tr>
                                        <td><a class="fw-semibold" href="{{ route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $curso->id]) }}">{{ $problema->nombre }}</a></td>
                                        <td class="text-center"><span class="badge text-bg-light border">{{ $problema->puntaje_total }} pts</span></td>
                                        <td>{{ $problema->categorias }}</td>
                                        <td class="text-center">
                                            @if($problema->resuelto)
                                                @if(!empty($problema->variacion_formato))
                                                    <span class="badge text-bg-success">Aceptado (Variación de Formato)</span>
                                                @else
                                                    <span class="badge text-bg-success">Resuelto</span>
                                                @endif
                                            @else
                                                <span class="badge text-bg-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td data-order="{{ \App\Support\DateHelper::timestamp($problema->created_at ?? ($problema->creado ?? null)) }}">{{ $problema->creado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 2: EVALUACIONES DEL CURSO -->
                <div id="seccion-evaluaciones" style="display: none;">
                    <div class="table-responsive">
                        <table id="table_evaluaciones" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-center">Problemas</th>
                                    <th class="text-center">Penalización</th>
                                    <th class="text-center">¿Finalizado?</th>
                                    <th class="text-center">Tiempo Desarrollo</th>
                                    <th class="text-center">Resueltos</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Término</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluaciones as $evaluacion)
                                    <tr>
                                        <td><a class="fw-semibold" href="{{ route('certamenes.ver', ['id_certamen' => $evaluacion->id]) }}">{{ $evaluacion->nombre }}</a></td>
                                        <td class="text-center">{{ $evaluacion->cantidad_problemas }}</td>
                                        <td class="text-center">{{ $evaluacion->penalizacion_error }}</td>
                                        <td class="text-center">
                                            @if($evaluacion->estado_finalizado)
                                                <span class="badge text-bg-success">Sí</span>
                                            @else
                                                <span class="badge text-bg-light border">No</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ isset($evaluacion->estado_finalizado) && isset($evaluacion->tiempo_desarrollo) ? gmdate('H:i:s', $evaluacion->tiempo_desarrollo) : "-" }}</td>
                                        <td class="text-center">{{ isset($evaluacion->maximo_resuelto) ? $evaluacion->maximo_resuelto . '/' . $evaluacion->cantidad_problemas : '-/' . $evaluacion->cantidad_problemas }}</td>
                                        <td data-order="{{ \App\Support\DateHelper::timestamp($evaluacion->fecha_inicio ?? null) }}">{{ $evaluacion->fecha_inicio_formatted ?? $evaluacion->fecha_inicio }}</td>
                                        <td data-order="{{ \App\Support\DateHelper::timestamp($evaluacion->fecha_termino ?? null) }}">{{ $evaluacion->fecha_termino_formatted ?? $evaluacion->fecha_termino }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}?v={{ @filemtime(public_path('assets/js/DataTables/gestion_initialize_es_cl.js')) ?: '1.0' }}"></script>
    <script>
        var cursoTabStorageKey = 'tb_curso_tab_{{ $curso->id }}';
        function mostrarSeccion(tipo) {
            try { localStorage.setItem(cursoTabStorageKey, tipo); } catch (e) {}
            if (tipo === 'problemas') {
                $('#seccion-problemas').show();
                $('#seccion-evaluaciones').hide();
                $('#btn-problemas').addClass('btn-danger active').removeClass('btn-outline-danger');
                $('#btn-evaluaciones').addClass('btn-outline-danger').removeClass('btn-danger active');
                if (typeof $.fn !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    $('#table').DataTable().columns.adjust().responsive.recalc();
                }
            } else {
                $('#seccion-problemas').hide();
                $('#seccion-evaluaciones').show();
                $('#btn-evaluaciones').addClass('btn-danger active').removeClass('btn-outline-danger');
                $('#btn-problemas').addClass('btn-outline-danger').removeClass('btn-danger active');
                if (typeof $.fn !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_evaluaciones')) {
                    $('#table_evaluaciones').DataTable().columns.adjust().responsive.recalc();
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var savedTab = localStorage.getItem(cursoTabStorageKey);
                if (savedTab === 'evaluaciones' || savedTab === 'problemas') {
                    mostrarSeccion(savedTab);
                }
            } catch (e) {}
        });
    </script>
@endpush
