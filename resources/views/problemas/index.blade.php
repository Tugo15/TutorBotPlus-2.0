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
                                <script>
                                    function switchVista(mode) {
                                        var vistaCarpetas = document.getElementById('vista-carpetas');
                                        var vistaTodos = document.getElementById('vista-todos');
                                        var btnCarpetas = document.getElementById('btnVistaCarpetas');
                                        var btnTodos = document.getElementById('btnVistaTodos');
                                        if (!vistaCarpetas || !vistaTodos) return;
                                        if (mode === 'todos') {
                                            vistaCarpetas.classList.add('d-none');
                                            vistaCarpetas.style.display = 'none';
                                            vistaTodos.classList.remove('d-none');
                                            vistaTodos.style.display = 'block';
                                            if (btnCarpetas) {
                                                btnCarpetas.classList.remove('btn-primary', 'active');
                                                btnCarpetas.classList.add('btn-outline-primary');
                                            }
                                            if (btnTodos) {
                                                btnTodos.classList.remove('btn-outline-primary');
                                                btnTodos.classList.add('btn-primary', 'active');
                                            }
                                            try {
                                                localStorage.setItem('vista_problemas_pref', 'todos');
                                                var url = new URL(window.location.href);
                                                url.searchParams.set('vista', 'todos');
                                                window.history.replaceState({}, '', url.toString());
                                            } catch(e) {}
                                            if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                                                setTimeout(function() {
                                                    try {
                                                        var dt = $('#table').DataTable();
                                                        dt.columns.adjust();
                                                        if (dt.responsive && typeof dt.responsive.recalc === 'function') dt.responsive.recalc();
                                                    } catch(e) {}
                                                }, 50);
                                            }
                                        } else {
                                            vistaTodos.classList.add('d-none');
                                            vistaTodos.style.display = 'none';
                                            vistaCarpetas.classList.remove('d-none');
                                            vistaCarpetas.style.display = 'block';
                                            if (btnTodos) {
                                                btnTodos.classList.remove('btn-primary', 'active');
                                                btnTodos.classList.add('btn-outline-primary');
                                            }
                                            if (btnCarpetas) {
                                                btnCarpetas.classList.remove('btn-outline-primary');
                                                btnCarpetas.classList.add('btn-primary', 'active');
                                            }
                                            try {
                                                localStorage.setItem('vista_problemas_pref', 'carpetas');
                                                var url = new URL(window.location.href);
                                                url.searchParams.delete('vista');
                                                window.history.replaceState({}, '', url.toString());
                                            } catch(e) {}
                                        }
                                    }
                                </script>
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
                            <div class="bg-gray-100 p-2.5 rounded-3 mb-2 border">
                                <!-- Fila 1: Buscador general amplio -->
                                <div class="row g-2 mb-2">
                                    <div class="col-12">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-xs text-secondary"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="buscadorProblemasDisponibles" placeholder="Buscar por nombre, código o categoría...">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-0 border-start-0 d-none" id="btnLimpiarBuscadorTexto" title="Limpiar texto">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fila 2: Filtros desplegables y botón limpiar -->
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <select class="form-select form-select-sm text-xs" id="filtroCatProbDisponibles" style="padding-right: 2rem;">
                                            <option value="">Todas las categorías</option>
                                            @if(isset($categorias_filtro))
                                                @foreach($categorias_filtro as $cat)
                                                    <option value="{{ $cat->nombre }}">{{ $cat->nombre }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3">
                                        <select class="form-select form-select-sm text-xs" id="filtroDifProbDisponibles" style="padding-right: 2.2rem; min-width: 130px;">
                                            <option value="">Dificultad: Todas</option>
                                            <option value="facil">Fácil</option>
                                            <option value="medio">Medio</option>
                                            <option value="dificil">Difícil</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3">
                                        <select class="form-select form-select-sm text-xs" id="filtroEstadoProbDisponibles" style="padding-right: 2.2rem; min-width: 130px;">
                                            <option value="">Visibilidad: Todas</option>
                                            <option value="visible">Solo Visibles</option>
                                            <option value="oculto">Solo Ocultos</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-2">
                                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0 w-100" id="btnLimpiarFiltrosProbDisp" title="Restablecer todos los filtros">
                                            <i class="fa fa-undo me-1"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 px-1 flex-wrap gap-2">
                                <small class="text-xxs text-secondary">
                                    Mostrando <strong id="countVisiblesProb" class="text-dark">{{ isset($problemas_disponibles_para_asignar) ? $problemas_disponibles_para_asignar->count() : 0 }}</strong> de <strong>{{ isset($problemas_disponibles_para_asignar) ? $problemas_disponibles_para_asignar->count() : 0 }}</strong> problemas disponibles
                                </small>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xxs btn-outline-secondary mb-0 py-1 px-2" id="btnDeseleccionarTodosProbDisponibles">
                                        Deseleccionar todos
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary mb-0" id="btnMarcarTodosProbDisponibles">
                                        <i class="fa fa-check-double me-1"></i> Seleccionar visibles
                                    </button>
                                </div>
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
                                            @php
                                                $esVisibleProb = (bool)$probDisp->visible;
                                                $difClass = $probDisp->dificultad == 'Fácil' ? 'bg-success' : ($probDisp->dificultad == 'Medio' ? 'bg-warning' : 'bg-danger');
                                            @endphp
                                            <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 cursor-pointer item-problema-disp"
                                                   data-id="{{ $probDisp->id }}"
                                                   data-nombre="{{ $probDisp->nombre }}"
                                                   data-codigo="{{ $probDisp->codigo }}"
                                                   data-categorias="{{ mb_strtolower($probDisp->categorias->pluck('nombre')->implode(' ')) }}"
                                                   data-dificultad="{{ mb_strtolower($probDisp->dificultad ?? '') }}"
                                                   data-visible="{{ $esVisibleProb ? 'visible' : 'oculto' }}">
                                                <div class="d-flex align-items-center text-truncate me-2">
                                                    <input class="form-check-input me-3 check-prob-disp" type="checkbox" name="problemas[]" value="{{ $probDisp->id }}">
                                                    <div class="text-truncate">
                                                        <div class="d-flex align-items-center gap-1 text-truncate">
                                                            <strong class="text-xs text-dark text-truncate">{{ $probDisp->nombre }}</strong>
                                                            @if($esVisibleProb)
                                                                <span class="badge border border-success text-success bg-white text-xxs px-1 py-0 ms-1" title="Visible para estudiantes">
                                                                    <i class="fa fa-eye me-0.5"></i> Visible
                                                                </span>
                                                            @else
                                                                <span class="badge border border-secondary text-secondary bg-white text-xxs px-1 py-0 ms-1 opacity-8" title="Oculto para estudiantes">
                                                                    <i class="fa fa-eye-slash me-0.5"></i> Oculto
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                                            <span class="badge border border-primary text-primary bg-white text-xxs font-monospace">{{ $probDisp->codigo }}</span>
                                                            @foreach($probDisp->categorias as $c)
                                                                <span class="badge bg-light text-secondary text-xxs border">{{ $c->nombre }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge {{ $difClass }} text-xxs ms-2">
                                                    {{ $probDisp->dificultad ?: 'Sin dificultad' }}
                                                </span>
                                            </label>
                                        @endforeach
                                        <div id="sinResultadosProblemasDisponibles" class="text-xs text-secondary text-center py-4 d-none">
                                            <i class="fa fa-search text-muted fa-2x mb-2 d-block"></i>
                                            No se encontraron problemas que coincidan con los filtros aplicados.
                                        </div>
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
        window.switchVista = function(mode) {
            var vistaCarpetas = document.getElementById('vista-carpetas');
            var vistaTodos = document.getElementById('vista-todos');
            var btnCarpetas = document.getElementById('btnVistaCarpetas');
            var btnTodos = document.getElementById('btnVistaTodos');

            if (!vistaCarpetas || !vistaTodos) return;

            if (mode === 'todos') {
                vistaCarpetas.classList.add('d-none');
                vistaCarpetas.style.display = 'none';
                vistaTodos.classList.remove('d-none');
                vistaTodos.style.display = 'block';
                
                if (btnCarpetas) {
                    btnCarpetas.classList.remove('btn-primary', 'active');
                    btnCarpetas.classList.add('btn-outline-primary');
                }
                if (btnTodos) {
                    btnTodos.classList.remove('btn-outline-primary');
                    btnTodos.classList.add('btn-primary', 'active');
                }

                try {
                    localStorage.setItem('vista_problemas_pref', 'todos');
                    var url = new URL(window.location.href);
                    url.searchParams.set('vista', 'todos');
                    window.history.replaceState({}, '', url.toString());
                } catch(e) {}

                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    setTimeout(function() {
                        try {
                            var dt = $('#table').DataTable();
                            dt.columns.adjust();
                            if (dt.responsive && typeof dt.responsive.recalc === 'function') {
                                dt.responsive.recalc();
                            }
                        } catch(err) {}
                    }, 50);
                }
            } else {
                vistaTodos.classList.add('d-none');
                vistaTodos.style.display = 'none';
                vistaCarpetas.classList.remove('d-none');
                vistaCarpetas.style.display = 'block';
                
                if (btnTodos) {
                    btnTodos.classList.remove('btn-primary', 'active');
                    btnTodos.classList.add('btn-outline-primary');
                }
                if (btnCarpetas) {
                    btnCarpetas.classList.remove('btn-outline-primary');
                    btnCarpetas.classList.add('btn-primary', 'active');
                }

                try {
                    localStorage.setItem('vista_problemas_pref', 'carpetas');
                    var url = new URL(window.location.href);
                    url.searchParams.delete('vista');
                    window.history.replaceState({}, '', url.toString());
                } catch(e) {}
            }
        };
        var switchVista = window.switchVista;

        document.addEventListener("DOMContentLoaded", function() {
            var pref = localStorage.getItem('vista_problemas_pref');
            var urlParams = new URLSearchParams(window.location.search);
            var vistaParam = urlParams.get('vista');
            
            if (vistaParam === 'todos' || (pref === 'todos' && !urlParams.has('id_curso') && vistaParam !== 'carpetas')) {
                switchVista('todos');
            }

            var btnCarpetas = document.getElementById('btnVistaCarpetas');
            var btnTodos = document.getElementById('btnVistaTodos');
            if (btnCarpetas) btnCarpetas.addEventListener('click', function() { switchVista('carpetas'); });
            if (btnTodos) btnTodos.addEventListener('click', function() { switchVista('todos'); });

            setTimeout(() => {
                if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                    var table = $('#table').DataTable();
                    
                    if ($('#custom-filters-src').length) {
                        $('#custom-filters-src').children().appendTo('.custom-filters-container');
                        $('#custom-filters-src').remove();
                    }

                    var storageKeyProblemas = typeof window.getTutorBotFilterKey === 'function'
                        ? window.getTutorBotFilterKey('problemas_filters')
                        : 'tb_filters_problemas';

                    function saveProblemasFilters(estado, cursos, categorias) {
                        try {
                            localStorage.setItem(storageKeyProblemas, JSON.stringify({
                                estado: estado,
                                cursos: cursos,
                                categorias: categorias
                            }));
                        } catch (e) {}
                    }

                    function updateProblemasLabels(rawCursos, rawCats) {
                        var $lblCur = $('.lbl-curso-selected');
                        if ($lblCur.length) {
                            if (rawCursos.length === 0) $lblCur.html('Seleccionar...');
                            else if (rawCursos.length === 1) $lblCur.html(rawCursos[0] + ' <span class="badge bg-primary text-xxs ms-1">1</span>');
                            else $lblCur.html(rawCursos.length + ' selec. <span class="badge bg-primary text-xxs ms-1">' + rawCursos.length + '</span>');
                        }
                        var $lblCat = $('.lbl-cat-selected');
                        if ($lblCat.length) {
                            if (rawCats.length === 0) $lblCat.html('Seleccionar...');
                            else if (rawCats.length === 1) $lblCat.html(rawCats[0] + ' <span class="badge bg-primary text-xxs ms-1">1</span>');
                            else $lblCat.html(rawCats.length + ' selec. <span class="badge bg-primary text-xxs ms-1">' + rawCats.length + '</span>');
                        }
                    }

                    function applyProblemasFilters(redrawFull) {
                        var estado = $('#filterEstado').val() || '';
                        var rawCursos = $('.filter-curso-chk:checked').map(function() { return this.value; }).get();
                        var rawCats = $('.filter-categoria-chk:checked').map(function() { return this.value; }).get();
                        var curRegex = rawCursos.length > 0 ? '(' + rawCursos.map(function(v) { return $.fn.dataTable.util.escapeRegex(v); }).join('|') + ')' : '';
                        var catRegex = rawCats.length > 0 ? '(' + rawCats.map(function(v) { return $.fn.dataTable.util.escapeRegex(v); }).join('|') + ')' : '';

                        table.column(1).search(curRegex, true, false);
                        table.column(2).search(catRegex, true, false);
                        table.column(4).search(estado);
                        if (redrawFull) {
                            table.draw();
                        } else {
                            table.draw(false);
                        }
                        updateProblemasLabels(rawCursos, rawCats);
                        saveProblemasFilters(estado, rawCursos, rawCats);
                    }

                    try {
                        var savedProbRaw = localStorage.getItem(storageKeyProblemas);
                        if (savedProbRaw) {
                            var savedProb = JSON.parse(savedProbRaw);
                            if (savedProb && typeof savedProb.estado === 'string') {
                                $('#filterEstado').val(savedProb.estado);
                            }
                            if (savedProb && Array.isArray(savedProb.cursos)) {
                                $('.filter-curso-chk').each(function() {
                                    $(this).prop('checked', savedProb.cursos.indexOf(this.value) !== -1);
                                });
                            }
                            if (savedProb && Array.isArray(savedProb.categorias)) {
                                $('.filter-categoria-chk').each(function() {
                                    $(this).prop('checked', savedProb.categorias.indexOf(this.value) !== -1);
                                });
                            }
                        }
                    } catch (e) {}

                    applyProblemasFilters(false);
                    
                    $('#filterEstado').on('change', function() {
                        applyProblemasFilters(true);
                    });
                    
                    $(document).on('change', '.filter-curso-chk, .filter-categoria-chk', function() {
                        applyProblemasFilters(true);
                    });
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

        // Filtro multifuncional y contador para modal de asignación de problemas
        document.addEventListener('DOMContentLoaded', function() {
            function normalizarTexto(str) {
                if (!str) return '';
                return str.toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            const buscadorProb = document.getElementById('buscadorProblemasDisponibles');
            const btnLimpiarBuscador = document.getElementById('btnLimpiarBuscadorTexto');
            const filtroCatProb = document.getElementById('filtroCatProbDisponibles');
            const filtroDifProb = document.getElementById('filtroDifProbDisponibles');
            const filtroEstadoProb = document.getElementById('filtroEstadoProbDisponibles');
            const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltrosProbDisp');
            const countVisibles = document.getElementById('countVisiblesProb');
            const modalFilterStorageKey = typeof window.getTutorBotFilterKey === 'function'
                ? window.getTutorBotFilterKey('problemas_modal_disp')
                : 'tb_filters_problemas_modal_disp_' + window.location.pathname;

            function saveModalFiltersState() {
                try {
                    localStorage.setItem(modalFilterStorageKey, JSON.stringify({
                        term: buscadorProb ? buscadorProb.value : '',
                        cat: filtroCatProb ? filtroCatProb.value : '',
                        dif: filtroDifProb ? filtroDifProb.value : '',
                        estado: filtroEstadoProb ? filtroEstadoProb.value : ''
                    }));
                } catch (e) {}
            }

            function aplicarFiltrosModal() {
                const termRaw = buscadorProb ? buscadorProb.value : '';
                const term = normalizarTexto(termRaw);
                const cat = normalizarTexto(filtroCatProb ? filtroCatProb.value : '');
                const dif = normalizarTexto(filtroDifProb ? filtroDifProb.value : '');
                const estado = normalizarTexto(filtroEstadoProb ? filtroEstadoProb.value : '');
                saveModalFiltersState();

                if (btnLimpiarBuscador) {
                    btnLimpiarBuscador.classList.toggle('d-none', !termRaw);
                }

                const terminos = term ? term.split(/\s+/).filter(Boolean) : [];
                const items = document.querySelectorAll('#listaProblemasDisponibles .item-problema-disp');
                let visibles = 0;

                items.forEach(function(item) {
                    const nombreText = normalizarTexto(item.getAttribute('data-nombre') || '');
                    const codigoText = normalizarTexto(item.getAttribute('data-codigo') || '');
                    const catsText = normalizarTexto(item.getAttribute('data-categorias') || '');
                    const difText = normalizarTexto(item.getAttribute('data-dificultad') || '');
                    const visibleText = normalizarTexto(item.getAttribute('data-visible') || '');

                    // Búsqueda multi-palabra sobre nombre, código, categorías y dificultad
                    const textoTotal = `${nombreText} ${codigoText} ${catsText} ${difText}`;
                    const coincideTexto = terminos.length === 0 || terminos.every(t => textoTotal.includes(t));

                    const coincideCat = !cat || catsText.includes(cat);
                    const coincideDif = !dif || difText.includes(dif);
                    const coincideEstado = !estado || visibleText === estado;

                    const coincide = coincideTexto && coincideCat && coincideDif && coincideEstado;

                    if (coincide) {
                        item.classList.remove('d-none');
                        item.style.removeProperty('display');
                        visibles++;
                    } else {
                        item.classList.add('d-none');
                        item.style.setProperty('display', 'none', 'important');
                    }
                });

                if (countVisibles) {
                    countVisibles.textContent = visibles.toString();
                }

                const sinRes = document.getElementById('sinResultadosProblemasDisponibles');
                if (sinRes) {
                    sinRes.classList.toggle('d-none', visibles > 0 || items.length === 0);
                }
            }

            if (buscadorProb) {
                let debounceBuscadorProb;
                buscadorProb.addEventListener('input', function() {
                    clearTimeout(debounceBuscadorProb);
                    debounceBuscadorProb = setTimeout(aplicarFiltrosModal, 180);
                });
                ['paste', 'search'].forEach(evento => {
                    buscadorProb.addEventListener(evento, aplicarFiltrosModal);
                });
            }

            if (btnLimpiarBuscador) {
                btnLimpiarBuscador.addEventListener('click', function() {
                    if (buscadorProb) {
                        buscadorProb.value = '';
                        buscadorProb.focus();
                    }
                    aplicarFiltrosModal();
                });
            }

            if (filtroCatProb) {
                filtroCatProb.addEventListener('change', aplicarFiltrosModal);
            }

            if (filtroDifProb) {
                filtroDifProb.addEventListener('change', aplicarFiltrosModal);
            }

            if (filtroEstadoProb) {
                filtroEstadoProb.addEventListener('change', aplicarFiltrosModal);
            }

            if (btnLimpiarFiltros) {
                btnLimpiarFiltros.addEventListener('click', function() {
                    if (buscadorProb) buscadorProb.value = '';
                    if (filtroCatProb) filtroCatProb.value = '';
                    if (filtroDifProb) filtroDifProb.value = '';
                    if (filtroEstadoProb) filtroEstadoProb.value = '';
                    try { localStorage.removeItem(modalFilterStorageKey); } catch (e) {}
                    aplicarFiltrosModal();
                });
            }

            try {
                const savedModalRaw = localStorage.getItem(modalFilterStorageKey);
                if (savedModalRaw) {
                    const savedModal = JSON.parse(savedModalRaw);
                    if (savedModal) {
                        if (buscadorProb && typeof savedModal.term === 'string') buscadorProb.value = savedModal.term;
                        if (filtroCatProb && typeof savedModal.cat === 'string') filtroCatProb.value = savedModal.cat;
                        if (filtroDifProb && typeof savedModal.dif === 'string') filtroDifProb.value = savedModal.dif;
                        if (filtroEstadoProb && typeof savedModal.estado === 'string') filtroEstadoProb.value = savedModal.estado;
                        aplicarFiltrosModal();
                    }
                }
            } catch (e) {}

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
                    if (!item.classList.contains('d-none') && item.style.display !== 'none') {
                        const ch = item.querySelector('.check-prob-disp');
                        if (ch) ch.checked = true;
                    }
                });
                actualizarContadorProbDisp();
            });

            document.getElementById('btnDeseleccionarTodosProbDisponibles')?.addEventListener('click', function() {
                const checks = document.querySelectorAll('.check-prob-disp');
                checks.forEach(function(ch) {
                    ch.checked = false;
                });
                actualizarContadorProbDisp();
            });
        });
    </script>
@endpush
