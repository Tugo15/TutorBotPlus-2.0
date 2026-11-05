@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="row align-items-center pb-2 gy-2">
                        <div class="col-12 col-lg"></div>
                        <div class="col-12 col-lg-auto text-center">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-primary"></i>Gestión de Problemas</h6>
                            <p class="text-xs text-secondary mb-0">Administración de problemas de programación organizados por asignaturas o listado general.</p>
                        </div>
                        <div class="col-12 col-lg d-flex justify-content-center justify-content-lg-end align-items-center gap-2 flex-wrap">
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
                                                        <div class="bg-light text-dark border rounded-circle text-center d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px;">
                                                            <i class="fa fa-folder text-primary fa-lg" style="top: 0; position: relative;"></i>
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
                                <div class="text-end d-flex justify-content-end align-items-center gap-2 flex-wrap" style="flex: 1;">
                                    @can('editar problemas')
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalAsignarProblemas">
                                            <i class="fa fa-folder-plus me-1"></i> Asignar Ejercicios Existentes
                                        </button>
                                    @endcan
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

    @if(isset($curso_activo) && $curso_activo)
        <!-- MODAL PARA ASIGNAR EJERCICIOS EXISTENTES AL CURSO -->
        <div class="modal fade" id="modalAsignarProblemas" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title font-weight-bold" id="modalAsignarLabel">
                            <i class="fa fa-folder-plus text-primary me-2"></i>Asignar Ejercicios al Curso: {{ $curso_activo->nombre }} ({{ $curso_activo->codigo }})
                        </h6>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('problemas.curso.agregar', ['id_curso' => $curso_activo->id]) }}" method="POST">
                        @csrf
                        <div class="modal-body p-3">
                            <p class="text-xs text-secondary mb-2">
                                Seleccione los problemas del banco general que desea incorporar a este curso:
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="max-width: 320px;">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscadorProblemasDisponibles" placeholder="Buscar por nombre, código o categoría...">
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary mb-0" id="btnMarcarTodosProbDisponibles">
                                    <i class="fa fa-check-double me-1"></i> Seleccionar todos los visibles
                                </button>
                            </div>

                            <div class="border rounded p-2" style="max-height: 350px; overflow-y: auto;">
                                @if(!isset($problemas_disponibles_para_asignar) || $problemas_disponibles_para_asignar->isEmpty())
                                    <p class="text-xs text-secondary text-center py-4 mb-0">
                                        <i class="fa fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                        Todos los problemas registrados en la plataforma ya están asignados a este curso.
                                    </p>
                                @else
                                    <div class="list-group list-group-flush" id="listaProblemasDisponibles">
                                        @foreach($problemas_disponibles_para_asignar as $probDisp)
                                            <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 cursor-pointer item-problema-disp">
                                                <div class="d-flex align-items-center text-truncate me-2">
                                                    <input class="form-check-input me-3 check-prob-disp" type="checkbox" name="problemas[]" value="{{ $probDisp->id }}">
                                                    <div class="text-truncate">
                                                        <strong class="text-xs text-dark d-block text-truncate">{{ $probDisp->nombre }}</strong>
                                                        <span class="badge border border-primary text-primary bg-white text-xxs font-monospace">{{ $probDisp->codigo }}</span>
                                                        @foreach($probDisp->categorias as $c)
                                                            <span class="badge bg-light text-secondary text-xxs">{{ $c->nombre }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span class="badge {{ $probDisp->dificultad == 'Fácil' ? 'bg-success' : ($probDisp->dificultad == 'Medio' ? 'bg-warning' : 'bg-danger') }} text-xxs">
                                                    {{ $probDisp->dificultad ?: 'Sin dificultad' }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer border-top py-2">
                            <span class="text-xs text-secondary me-auto">Seleccionados: <strong id="countProbSeleccionados">0</strong></span>
                            <button type="button" class="btn btn-xs btn-light mb-0" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-xs btn-dark mb-0" id="btnSubmitAgregarProblemas" disabled>
                                <i class="fa fa-plus me-1"></i> Asignar Ejercicios Seleccionados
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}?v={{ @filemtime(public_path('assets/js/DataTables/gestion_initialize_es_cl.js')) ?: '1.0' }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}?v={{ @filemtime(public_path('assets/js/alertas_administracion.js')) ?: '1.0' }}"></script> 
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

        function confirmarQuitarProblemaDeCurso(nombre, id) {
            Swal.fire({
                title: '¿Quitar ejercicio del curso?',
                text: 'Se desvinculará "' + nombre + '" de este curso. El ejercicio seguirá existiendo en el banco general de la plataforma.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, quitar del curso',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('quitarCursoForm_' + id).submit();
                }
            });
        }

        // Filtro y contador para modal de asignación de problemas
        document.addEventListener('DOMContentLoaded', function() {
            const buscadorProb = document.getElementById('buscadorProblemasDisponibles');
            if (buscadorProb) {
                buscadorProb.addEventListener('keyup', function() {
                    const term = this.value.toLowerCase().trim();
                    const items = document.querySelectorAll('#listaProblemasDisponibles .item-problema-disp');
                    items.forEach(function(item) {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(term) ? '' : 'none';
                    });
                });
            }

            document.querySelectorAll('.check-prob-disp').forEach(function(ch) {
                ch.addEventListener('change', actualizarContadorProbDisp);
            });

            function actualizarContadorProbDisp() {
                const checks = document.querySelectorAll('.check-prob-disp:checked');
                const countSpan = document.getElementById('countProbSeleccionados');
                const btnSubmit = document.getElementById('btnSubmitAgregarProblemas');
                if (countSpan) countSpan.textContent = checks.length;
                if (btnSubmit) btnSubmit.disabled = checks.length === 0;
            }

            document.getElementById('btnMarcarTodosProbDisponibles')?.addEventListener('click', function() {
                const visibleItems = document.querySelectorAll('#listaProblemasDisponibles .item-problema-disp');
                visibleItems.forEach(function(item) {
                    if (item.style.display !== 'none') {
                        const ch = item.querySelector('.check-prob-disp');
                        if (ch) ch.checked = true;
                    }
                });
                actualizarContadorProbDisp();
            });
        });
    </script>
@endpush
