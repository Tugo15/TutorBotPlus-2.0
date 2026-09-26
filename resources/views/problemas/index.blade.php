@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-primary"></i>Gestión de Problemas</h6>
                            <p class="text-xs text-secondary mb-0">Administración de problemas de programación organizados por asignaturas.</p>
                        </div>
                        @if(!isset($id_curso_activo) || !$id_curso_activo)
                            @can('crear problemas')
                                <a class="btn btn-sm btn-dark mb-0" href="{{ route('problemas.crear') }}">
                                    <i class="fa fa-plus me-1"></i> Crear Problema
                                </a>
                            @endcan
                        @endif
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
                                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-folder-open me-2 text-primary"></i>Cursos Asignados</h6>
                                    <p class="text-xs text-secondary mb-0">Seleccione un curso para gestionar sus problemas de programación.</p>
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
                                                            <i class="fa fa-folder text-primary fa-lg"></i>
                                                        </div>
                                                        <span class="badge border border-primary text-primary text-xxs px-2.5 py-1 bg-white">{{ $curso->codigo }}</span>
                                                    </div>
                                                    <h6 class="font-weight-bold text-dark mb-1 text-sm">{{ $curso->nombre }}</h6>
                                                    <p class="text-xs text-secondary mb-3">{{ $curso->problemas_count ?? 0 }} Problema(s)</p>
                                                </div>
                                                <div class="pt-2 border-top d-flex justify-content-between align-items-center gap-1">
                                                    <a href="{{ route('problemas.index', ['id_curso' => $curso->id]) }}" class="btn btn-xs btn-outline-primary mb-0 w-100">
                                                        <i class="fa fa-folder-open me-1"></i> Ver Problemas
                                                    </a>
                                                    @can('crear problemas')
                                                    <a href="{{ route('problemas.crear', ['id_curso' => $curso->id]) }}" class="btn btn-xs btn-dark mb-0" title="Crear Problema">
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
                        <!-- VISTA 2: DENTRO DE LA CARPETA DEL CURSO -->
                        <div class="px-4 pb-1">
                            <div class="d-flex justify-content-between align-items-center mb-1 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('problemas.index') }}" class="btn btn-xs btn-outline-secondary mb-0 me-3">
                                        <i class="fa fa-arrow-left me-1"></i> Volver a Cursos
                                    </a>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark">
                                            <i class="fa fa-folder-open me-2 text-primary"></i>Curso: <span class="text-dark">{{ $curso_activo->nombre ?? '' }}</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">Código: <strong>{{ $curso_activo->codigo ?? '' }}</strong></p>
                                    </div>
                                </div>
                                @can('crear problemas')
                                    <a class="btn btn-sm btn-dark mb-0" href="{{ route('problemas.crear', ['id_curso' => $curso_activo->id]) }}">
                                        <i class="fa fa-plus me-1"></i> Crear Problema
                                    </a>
                                @endcan
                            </div>
                        </div>

                        @php
                            $categorias_disponibles = collect();
                            foreach($problemas as $prob) {
                                foreach($prob->categorias as $cat) { $categorias_disponibles->put($cat->nombre, $cat->nombre); }
                            }
                            $categorias_disponibles = $categorias_disponibles->sort();
                        @endphp

                        <div id="custom-filters-src" class="d-none">
                            <div class="d-flex align-items-center">
                                <label for="filterEstado" class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Estado:</label>
                                <select id="filterEstado" class="form-select form-select-sm w-auto border-secondary" style="min-width: 100px;">
                                    <option value="">Todos</option>
                                    <option value="Visible">Visible</option>
                                    <option value="Oculto">Oculto</option>
                                </select>
                            </div>

                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Categoría:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        Seleccionar...
                                    </button>
                                    <ul class="dropdown-menu px-2" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($categorias_disponibles as $c)
                                            <li>
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input filter-categoria-chk" type="checkbox" value="{{ $c }}" id="chkCat_{{ $loop->index }}">
                                                    <label class="form-check-label text-sm mb-0" for="chkCat_{{ $loop->index }}">{{ $c }}</label>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="table" style="width:100%">
                                <thead>
                                    <tr class="border-bottom">
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Problema</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Categorías y Dificultad</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Lenguajes</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2 text-center">Estado</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Disponibilidad</th>
                                        @canany(['editar problemas', 'eliminar problemas', 'ver informe del problema'])
                                            <th class="none text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Acciones</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($problemas as $problema)
                                        <tr>
                                            <td>
                                                <div class="d-inline-flex align-items-center py-1">
                                                    <div class="d-flex flex-column justify-content-center ms-2">
                                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $problema->nombre }}</h6>
                                                        <p class="text-xs text-secondary mb-0 mt-1">Código: <span class="badge border border-secondary text-secondary text-xxs px-2 py-0 bg-white">{{ $problema->codigo }}</span></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1 py-1">
                                                    <div class="d-flex align-items-center gap-1">
                                                        @php
                                                            $difProbClass = ($problema->dificultad == 'Fácil') ? 'border-success text-success' : (($problema->dificultad == 'Difícil') ? 'border-danger text-danger' : 'border-warning text-warning');
                                                        @endphp
                                                        <span class="badge border {{ $difProbClass }} text-xxs bg-white">{{ $problema->dificultad ?? 'Medio' }}</span>
                                                        @foreach ($problema->categorias()->get() as $categoria)
                                                            <span class="badge border border-danger text-danger text-xxs bg-white">{{ $categoria->nombre }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $lenguaje)
                                                        <span class="badge border border-dark text-dark text-xxs bg-white">{{ $lenguaje }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="badge border {{ $problema->visible == true ? 'border-success text-success' : 'border-secondary text-secondary' }} text-xxs bg-white">
                                                        <i class="fa {{ $problema->visible == true ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i> {{ $problema->visible == true ? 'Visible' : 'Oculto' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="text-xs font-weight-bold text-dark">Inicio: {{ $problema->fecha_inicio ? $problema->fecha_inicio : 'Inmediata' }}</span>
                                                    <span class="text-xs text-secondary">Término: {{ $problema->fecha_termino ? $problema->fecha_termino : 'Indefinida' }}</span>
                                                </div>
                                            </td>
                                            @canany(['editar problemas', 'eliminar problemas', 'ver informe del problema'])
                                                <td class="align-middle text-end">
                                                    <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                                        @can('ver informe del problema')
                                                            <a class="btn btn-xs btn-outline-primary mb-0" title="Ver Informe"
                                                            href="{{ route('informes.problemas.index', ['id' => $problema->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                                        @endcan
                                                        @can('editar problemas')
                                                            <a class="btn btn-xs btn-outline-info mb-0" title="Casos de Prueba"
                                                                href="{{ route('casos_pruebas.assign', ['id' => $problema->id]) }}"><i class="fa fa-vials me-1"></i> Casos</a>
                                                            <a class="btn btn-xs btn-outline-secondary mb-0" title="Editorial"
                                                                href="{{ route('problemas.editorial', ['id' => $problema->id]) }}"><i class="fa fa-book me-1"></i> Editorial</a>
                                                            <a class="btn btn-xs btn-outline-warning mb-0" title="Editar Problema"
                                                                href="{{ route('problemas.editar', ['id' => $problema->id]) }}"><i class="fa fa-pencil me-1"></i> Editar</a>
                                                        @endcan
                                                        @can('eliminar problemas')
                                                            <form action="{{ route('problemas.eliminar', ['id' => $problema->id]) }}"
                                                                method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el problema '.$problema->nombre}}', {{$problema->id}})" id="eliminarForm_{{$problema->id}}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-outline-danger mb-0" title="Eliminar Problema"><i class="fa fa-fw fa-trash me-1"></i> Eliminar</button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                    
                    $('#filterEstado').on('change', function() {
                        table.column(3).search(this.value).draw();
                    });
                    
                    function updateCol1Filters() {
                        var categorias = $('.filter-categoria-chk:checked').map(function() { return this.value; }).get();
                        var catRegex = categorias.length > 0 ? '(' + categorias.join('|') + ')' : '';
                        table.column(1).search(catRegex, true, false).draw();
                    }
                    
                    $('.filter-categoria-chk').on('change', updateCol1Filters);
                }
            }, 500);
        });
    </script>
@endpush
