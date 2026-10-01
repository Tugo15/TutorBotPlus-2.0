@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;"></div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-primary"></i>Gestión de Problemas</h6>
                            <p class="text-xs text-secondary mb-0">Administración de problemas de programación organizados por asignaturas o listado general.</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-center justify-content-lg-end gap-2" style="flex: 1;">
                            @if(!isset($id_curso_activo) || !$id_curso_activo)
                                <div class="btn-group me-1" role="group" id="btnGroupVista" aria-label="Modo de Vista">
                                    <button type="button" class="btn btn-xs btn-primary active mb-0" id="btnVistaCarpetas" onclick="switchVista('carpetas')">
                                        <i class="fa fa-folder-open me-1"></i> Vista Carpetas
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary mb-0" id="btnVistaTodos" onclick="switchVista('todos')">
                                        <i class="fa fa-list me-1"></i> Todos
                                    </button>
                                </div>
                                @can('crear problemas')
                                    <a class="btn btn-xs btn-dark mb-0" href="{{ route('problemas.crear') }}">
                                        <i class="fa fa-plus me-1"></i> Problema
                                    </a>
                                @endcan
                            @endif
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
                        <div id="vista-carpetas" class="px-4 pb-4 mt-3">
                            <div class="mb-3 text-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-folder-open me-2 text-primary"></i>Cursos Asignados</h6>
                                <p class="text-xs text-secondary mb-0">Seleccione un curso para gestionar sus problemas de programación.</p>
                            </div>

                            <div class="row g-3">
                                @forelse ($cursos as $curso)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border shadow-xs h-100 hover-shadow transition">
                                            <div class="card-body p-3 d-flex flex-column justify-content-between text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 w-100">
                                                        <div class="icon icon-shape bg-light text-dark border rounded-circle text-center d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px;">
                                                            <i class="fa fa-folder text-primary fa-lg"></i>
                                                        </div>
                                                        <span class="badge border border-primary text-primary text-xxs px-2.5 py-1 bg-white">{{ $curso->codigo }}</span>
                                                    </div>
                                                    <h6 class="font-weight-bold text-dark mb-1 text-sm">{{ $curso->nombre }}</h6>
                                                    <p class="text-xs text-secondary mb-3">{{ $curso->problemas_count ?? 0 }} Problema(s)</p>
                                                </div>
                                                <div class="pt-2 border-top d-flex justify-content-center align-items-center gap-1">
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

                        <!-- VISTA 2: LISTADO DE TODOS LOS PROBLEMAS -->
                        <div id="vista-todos" class="d-none">
                            <div class="px-4 pt-3 pb-1">
                                <div class="mb-2 text-center">
                                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-list me-2 text-primary"></i>Listado General de Problemas</h6>
                                    <p class="text-xs text-secondary mb-0">Todos los problemas registrados en sus asignaturas.</p>
                                </div>
                            </div>
                            @include('problemas.componentes.tabla_problemas_list', ['problemas' => $problemas, 'mostrar_curso' => true, 'id_curso_activo' => null])
                        </div>
                    @else
                        <!-- VISTA 3: DENTRO DE LA CARPETA DE UN CURSO ESPECÍFICO -->
                        <div class="px-4 pb-1 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 pb-2 border-bottom flex-wrap gap-2">
                                <div style="flex: 1;">
                                    <a href="{{ route('problemas.index') }}" class="btn btn-xs btn-outline-secondary mb-0">
                                        <i class="fa fa-arrow-left me-1"></i> Volver a Cursos
                                    </a>
                                </div>
                                <div class="text-center" style="flex: 2;">
                                    <h6 class="mb-0 font-weight-bold text-dark">
                                        <i class="fa fa-folder-open me-2 text-primary"></i>Curso: <span class="text-dark">{{ $curso_activo->nombre ?? '' }}</span>
                                    </h6>
                                    <p class="text-xs text-secondary mb-0">Código: <strong>{{ $curso_activo->codigo ?? '' }}</strong></p>
                                </div>
                                <div class="text-end" style="flex: 1;">
                                    @can('crear problemas')
                                        <a class="btn btn-sm btn-dark mb-0" href="{{ route('problemas.crear', ['id_curso' => $curso_activo->id]) }}">
                                            <i class="fa fa-plus me-1"></i> Crear Problema
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        @include('problemas.componentes.tabla_problemas_list', ['problemas' => $problemas, 'mostrar_curso' => false, 'id_curso_activo' => $id_curso_activo])
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}?v={{ @filemtime(public_path('assets/js/DataTables/gestion_initialize_es_cl.js')) ?: '1.0' }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
    <script>
        function switchVista(mode) {
            var vistaCarpetas = document.getElementById('vista-carpetas');
            var vistaTodos = document.getElementById('vista-todos');
            var btnCarpetas = document.getElementById('btnVistaCarpetas');
            var btnTodos = document.getElementById('btnVistaTodos');

            if (!vistaCarpetas || !vistaTodos) return;

            if (mode === 'todos') {
                vistaCarpetas.classList.add('d-none');
                vistaTodos.classList.remove('d-none');
                
                if (btnCarpetas) {
                    btnCarpetas.classList.remove('btn-primary', 'active');
                    btnCarpetas.classList.add('btn-outline-primary');
                }
                if (btnTodos) {
                    btnTodos.classList.remove('btn-outline-primary');
                    btnTodos.classList.add('btn-primary', 'active');
                }

                localStorage.setItem('vista_problemas_pref', 'todos');

                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    setTimeout(function() {
                        $('#table').DataTable().columns.adjust().responsive.recalc();
                    }, 100);
                }
            } else {
                vistaTodos.classList.add('d-none');
                vistaCarpetas.classList.remove('d-none');
                
                if (btnTodos) {
                    btnTodos.classList.remove('btn-primary', 'active');
                    btnTodos.classList.add('btn-outline-primary');
                }
                if (btnCarpetas) {
                    btnCarpetas.classList.remove('btn-outline-primary');
                    btnCarpetas.classList.add('btn-primary', 'active');
                }

                localStorage.setItem('vista_problemas_pref', 'carpetas');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            var pref = localStorage.getItem('vista_problemas_pref');
            var urlParams = new URLSearchParams(window.location.search);
            var vistaParam = urlParams.get('vista');
            
            if (vistaParam === 'todos' || (pref === 'todos' && !urlParams.has('id_curso') && vistaParam !== 'carpetas')) {
                switchVista('todos');
            }

            setTimeout(() => {
                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    var table = $('#table').DataTable();
                    
                    if ($('#custom-filters-src').length) {
                        $('#custom-filters-src').children().appendTo('.custom-filters-container');
                        $('#custom-filters-src').remove();
                    }
                    
                    $('#filterEstado').on('change', function() {
                        table.column(4).search(this.value).draw();
                    });
                    
                    function updateColCursoFilters() {
                        var cursos = $('.filter-curso-chk:checked').map(function() { return this.value; }).get();
                        var curRegex = cursos.length > 0 ? '(' + cursos.join('|') + ')' : '';
                        table.column(1).search(curRegex, true, false).draw();
                        
                        var $lbl = $('.lbl-curso-selected');
                        if ($lbl.length) {
                            if (cursos.length === 0) {
                                $lbl.text('Seleccionar...');
                            } else if (cursos.length === 1) {
                                $lbl.text(cursos[0]);
                            } else {
                                $lbl.text(cursos.length + ' selec.');
                            }
                        }
                    }
                    
                    $(document).on('change', '.filter-curso-chk', updateColCursoFilters);
                    
                    function updateCol1Filters() {
                        var categorias = $('.filter-categoria-chk:checked').map(function() { return this.value; }).get();
                        var catRegex = categorias.length > 0 ? '(' + categorias.join('|') + ')' : '';
                        table.column(2).search(catRegex, true, false).draw();
                        
                        var $lbl = $('.lbl-cat-selected');
                        if ($lbl.length) {
                            if (categorias.length === 0) {
                                $lbl.text('Seleccionar...');
                            } else if (categorias.length === 1) {
                                $lbl.text(categorias[0]);
                            } else {
                                $lbl.text(categorias.length + ' selec.');
                            }
                        }
                    }
                    
                    $(document).on('change', '.filter-categoria-chk', updateCol1Filters);
                }
            }, 500);
        });
    </script>
@endpush
