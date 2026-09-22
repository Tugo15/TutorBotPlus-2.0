@extends('layout_plataforma.app', ['title_html' => 'Problemas', 'title' => $curso->nombre, 'breadcrumbs' => [["nombre" => "Cursos", "route" => route("cursos.listado")], ["nombre" => $curso->nombre]]])

@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        
        <div class="card border-danger">
            <div class="card-body px-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="ms-2 mb-0"><strong>Curso: {{ $curso->nombre }}</strong></h5>
                    <div class="btn-group shadow-sm" role="group" aria-label="Selección de vista">
                        <button type="button" class="btn btn-danger btn-md px-4 font-weight-bold active" id="btn-problemas" onclick="mostrarSeccion('problemas')">
                            <i class="fa fa-code me-1"></i> Problemas de Práctica ({{ $problemas->count() }})
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-md px-4 font-weight-bold" id="btn-evaluaciones" onclick="mostrarSeccion('evaluaciones')">
                            <i class="fa fa-file-text me-1"></i> Evaluaciones del Curso ({{ $evaluaciones->count() }})
                        </button>
                    </div>
                </div>
                <hr>

                <!-- VISTA 1 PRINCIPAL: PROBLEMAS DE PRÁCTICA DEL CURSO -->
                <div id="seccion-problemas">
                    <div class="table-responsive">
                        <table id="table" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Puntos</th>
                                    <th>Categoria</th>
                                    <th>¿Resuelto?</th>
                                    <th>Creado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($problemas as $problema)
                                    <tr>
                                        <td><a href="{{ route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $curso->id]) }}">{{ $problema->nombre }}</a></td>
                                        <td>{{ $problema->puntaje_total }}</td>
                                        <td>{{ $problema->categorias }}</td>
                                        <td>{{ $problema->resuelto ? 'Si' : 'No' }}</td>
                                        <td>{{ $problema->creado }}</td>
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
                                    <th>Cantidad Problemas</th>
                                    <th>Penalización por error</th>
                                    <th>¿Finalizado?</th>
                                    <th>Tiempo de Desarrollo</th>
                                    <th>Ejercicios Resueltos</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Termino</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluaciones as $evaluacion)
                                    <tr>
                                        <td><a href="{{ route('certamenes.ver', ['id_certamen' => $evaluacion->id]) }}">{{ $evaluacion->nombre }}</a></td>
                                        <td>{{ $evaluacion->cantidad_problemas }}</td>
                                        <td>{{ $evaluacion->penalizacion_error }}</td>
                                        <td>{{ $evaluacion->estado_finalizado ? 'Si' : 'No' }}</td>
                                        <td>{{ isset($evaluacion->estado_finalizado) && isset($evaluacion->tiempo_desarrollo) ? gmdate('H:i:s', $evaluacion->tiempo_desarrollo) : "-" }}</td>
                                        <td>{{ isset($evaluacion->maximo_resuelto) ? $evaluacion->maximo_resuelto . '/' . $evaluacion->cantidad_problemas : '-/' . $evaluacion->cantidad_problemas }}</td>
                                        <td>{{ $evaluacion->fecha_inicio_formatted ?? $evaluacion->fecha_inicio }}</td>
                                        <td>{{ $evaluacion->fecha_termino_formatted ?? $evaluacion->fecha_termino }}</td>
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
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script>
        function mostrarSeccion(tipo) {
            if (tipo === 'problemas') {
                $('#seccion-problemas').show();
                $('#seccion-evaluaciones').hide();
                $('#btn-problemas').addClass('btn-danger active').removeClass('btn-outline-danger');
                $('#btn-evaluaciones').addClass('btn-outline-danger').removeClass('btn-danger active');
            } else {
                $('#seccion-problemas').hide();
                $('#seccion-evaluaciones').show();
                $('#btn-evaluaciones').addClass('btn-danger active').removeClass('btn-outline-danger');
                $('#btn-problemas').addClass('btn-outline-danger').removeClass('btn-danger active');
            }
        }
    </script>
@endpush
