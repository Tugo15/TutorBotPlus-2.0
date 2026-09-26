@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Evaluaciones'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Evaluaciones'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-check me-2 text-warning"></i>Gestión de Evaluaciones</h6>
                            <p class="text-xs text-secondary mb-0">Administración de certámenes organizados por asignaturas.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('error') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('success') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (!isset($id_curso_activo) || !$id_curso_activo)
                        <!-- VISTA 1: CARPETAS DE CURSOS -->
                        <div class="px-4 pb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-folder-open me-2 text-warning"></i>Cursos Asignados</h6>
                                    <p class="text-xs text-secondary mb-0">Seleccione un curso para gestionar sus evaluaciones.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                @forelse ($cursos as $curso)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border shadow-xs h-100 hover-shadow transition">
                                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="icon icon-shape bg-light text-dark border rounded-circle text-center d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px;">
                                                            <i class="fa fa-folder text-warning fa-lg"></i>
                                                        </div>
                                                        <span class="badge border border-primary text-primary text-xxs px-2.5 py-1 bg-white">{{ $curso->codigo }}</span>
                                                    </div>
                                                    <h6 class="font-weight-bold text-dark mb-1 text-sm">{{ $curso->nombre }}</h6>
                                                    <p class="text-xs text-secondary mb-3">{{ $curso->certamenes_count ?? 0 }} Evaluación(es)</p>
                                                </div>
                                                <div class="pt-2 border-top d-flex justify-content-between align-items-center gap-1">
                                                    <a href="{{ route('certamen.index', ['id_curso' => $curso->id]) }}" class="btn btn-xs btn-outline-warning mb-0 w-100">
                                                        <i class="fa fa-folder-open me-1"></i> Ver Evaluaciones
                                                    </a>
                                                    @can('crear certamen')
                                                    <a href="{{ route('certamen.crear', ['id_curso' => $curso->id]) }}" class="btn btn-xs btn-dark mb-0" title="Crear Evaluación">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="fa fa-folder-open text-muted fa-3x mb-3"></i>
                                        <h6 class="text-secondary mb-0">No hay cursos registrados.</h6>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <!-- VISTA 2: DENTRO DEL CURSO -->
                        <div class="px-4 pb-1">
                            <div class="d-flex justify-content-between align-items-center mb-1 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('certamen.index') }}" class="btn btn-xs btn-outline-secondary mb-0 me-3">
                                        <i class="fa fa-arrow-left me-1"></i> Volver a Cursos
                                    </a>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark">
                                            <i class="fa fa-folder-open me-2 text-warning"></i>Curso: <span class="text-dark">{{ $curso_activo->nombre ?? '' }}</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">Código: <strong>{{ $curso_activo->codigo ?? '' }}</strong></p>
                                    </div>
                                </div>
                                @can('crear certamen')
                                    <a class="btn btn-sm btn-dark mb-0" href="{{ route('certamen.crear', ['id_curso' => $curso_activo->id]) }}">
                                        <i class="fa fa-plus me-1"></i> Crear Evaluación
                                    </a>
                                @endcan
                            </div>
                        </div>

                        <div id="custom-filters-src" class="d-none">
                            <div class="d-flex align-items-center">
                                <label for="filterDificultad" class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Dificultad:</label>
                                <select id="filterDificultad" class="form-select form-select-sm w-auto border-secondary" style="min-width: 100px;">
                                    <option value="">Todas</option>
                                    <option value="Fácil">Fácil</option>
                                    <option value="Medio">Medio</option>
                                    <option value="Difícil">Difícil</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table">
                                <thead>
                                    <tr class="border-bottom">
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Evaluación</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Curso y Dificultad</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Acceso Red</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2 text-center">Configuración</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Disponibilidad</th>
                                        @canany(['editar certamen', 'eliminar certamen', 'ver informe del certamen'])
                                            <th class="none text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Acciones</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($certamenes as $certamen)
                                        <tr>
                                            <td>
                                                <div class="d-inline-flex align-items-center py-1">
                                                    <div class="d-flex flex-column justify-content-center ms-2">
                                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $certamen->nombre }}</h6>
                                                        <p class="text-xs text-secondary mb-0 mt-1">ID: <span class="badge border border-secondary text-secondary text-xxs px-2 py-0 bg-white">#{{ $certamen->id }}</span></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1 py-1">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge border border-primary text-primary text-xxs bg-white">{{ $certamen->curso->codigo }}</span>
                                                        @php
                                                            $difBadgeClass = ($certamen->dificultad == 'Fácil') ? 'border-success text-success' : (($certamen->dificultad == 'Difícil') ? 'border-danger text-danger' : 'border-warning text-warning');
                                                        @endphp
                                                        <span class="badge border {{ $difBadgeClass }} text-xxs bg-white">{{ $certamen->dificultad ?? 'Medio' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($certamen->restriccion_red)
                                                    <span class="badge border border-warning text-dark text-xxs bg-white"><i class="fa fa-university me-1 text-warning"></i>Red Institucional</span>
                                                @else
                                                    <span class="badge border border-info text-info text-xxs bg-white"><i class="fa fa-globe me-1"></i>Acceso Libre</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="text-xs text-dark font-weight-bold">
                                                        <i class="fa fa-list-ol text-secondary me-1"></i> {{ $certamen->cantidad_problemas }} Ejercicio(s)
                                                    </span>
                                                    <span class="text-xxs text-secondary">
                                                        Penalización: {{ $certamen->penalizacion_error ?? 0 }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="text-xs font-weight-bold text-dark">Inicio: {{ $certamen->fecha_inicio ? $certamen->fecha_inicio : 'No Definido' }}</span>
                                                    <span class="text-xs text-secondary">Término: {{ $certamen->fecha_termino ? $certamen->fecha_termino : 'No Definido' }}</span>
                                                </div>
                                            </td>
                                            @canany(['editar problemas', 'eliminar problemas', 'editar certamen', 'crear certamen'])
                                                <td class="align-middle text-end">
                                                    <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                                        @can('ver informe del certamen')
                                                            <a class="btn btn-xs btn-outline-primary mb-0" title="Ver Informe" href="{{ route('informe.certamen', ['id_certamen' => $certamen->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                                        @endcan
                                                        @can('editar certamen')
                                                            <a class="btn btn-xs btn-outline-info mb-0" title="Banco de Problemas" href="{{ route('certamen.banco_problemas', ['id_certamen' => $certamen->id]) }}"><i class="fa fa-database me-1"></i> Banco</a>
                                                            <a class="btn btn-xs btn-outline-warning mb-0" title="Editar Evaluación" href="{{ route('certamen.editar', ['id' => $certamen->id]) }}"><i class="fa fa-pencil me-1"></i> Editar</a>
                                                        @endcan
                                                        @can('crear certamen')
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mb-0" data-bs-toggle="modal" data-bs-target="#duplicarModal_{{ $certamen->id }}" title="Duplicar Evaluación">
                                                                <i class="fa fa-clone me-1"></i> Duplicar
                                                            </button>
                                                        @endcan
                                                        @can('eliminar certamen')
                                                            <form action="{{ route('certamen.eliminar', ['id' => $certamen->id]) }}" method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el certamen '.$certamen->nombre}}', {{$certamen->id}})" id="eliminarForm_{{$certamen->id}}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-outline-danger mb-0" title="Eliminar Evaluación"><i class="fa fa-fw fa-trash me-1"></i> Eliminar</button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcanany
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-secondary">
                                                No hay evaluaciones registradas en este curso.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @can('crear certamen')
                            @foreach ($certamenes as $certamen)
                                <!-- Modal Duplicar Certamen #{{ $certamen->id }} -->
                                <div class="modal fade text-start" id="duplicarModal_{{ $certamen->id }}" tabindex="-1" aria-labelledby="duplicarModalLabel_{{ $certamen->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('certamen.duplicar') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id_certamen" value="{{ $certamen->id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-weight-bold text-dark" id="duplicarModalLabel_{{ $certamen->id }}"><i class="fa fa-clone text-warning me-2"></i>Duplicar Evaluación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-sm text-secondary mb-3">Se creará una copia idéntica de la evaluación <strong>{{ $certamen->nombre }}</strong>. Seleccione el curso de destino:</p>
                                                    <div class="form-group">
                                                        <label for="id_curso_{{ $certamen->id }}" class="form-control-label font-weight-bold text-sm">Curso / Sección Destino*</label>
                                                        <select class="form-select" name="id_curso" id="id_curso_{{ $certamen->id }}" required>
                                                            @foreach ($cursos as $c)
                                                                <option value="{{ $c->id }}" {{ (isset($curso_activo) && $c->id == $curso_activo->id) || ($c->id == $certamen->id_curso) ? 'selected' : '' }}>
                                                                    {{ $c->nombre }} ({{ $c->codigo }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-sm btn-dark"><i class="fa fa-copy me-1"></i> Duplicar Evaluación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    var table = $('#table').DataTable();
                    
                    $('#custom-filters-src').children().appendTo('.custom-filters-container');
                    $('#custom-filters-src').remove();
                    
                    $('#filterDificultad').on('change', function() {
                        table.column(1).search(this.value).draw();
                    });
                }
            }, 500);
        });
    </script>
@endpush
