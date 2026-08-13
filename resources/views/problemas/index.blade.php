@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-primary"></i>Gestión de Problemas</h6>
                            <p class="text-xs text-secondary mb-0">Administre los problemas de programación y sus configuraciones.</p>
                        </div>
                        @can('crear problemas')
                        <a class="btn btn-sm btn-primary mb-0" href="{{ route('problemas.crear') }}"><i class="fa fa-plus me-1"></i> Crear</a>
                        @endcan
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
                    @php
                        $cursos_disponibles = collect();
                        $categorias_disponibles = collect();
                        foreach($problemas as $prob) {
                            foreach($prob->cursos as $cur) { $cursos_disponibles->put($cur->codigo, $cur->codigo); }
                            foreach($prob->categorias as $cat) { $categorias_disponibles->put($cat->nombre, $cat->nombre); }
                        }
                        $cursos_disponibles = $cursos_disponibles->sort();
                        $categorias_disponibles = $categorias_disponibles->sort();
                    @endphp

                    <div id="custom-filters-src" class="d-none">
                        <div class="d-flex align-items-center">
                            <label for="filterEstado" class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Estado:</label>
                            <select id="filterEstado" class="form-select form-select-sm w-auto" style="min-width: 100px;">
                                <option value="">Todos</option>
                                <option value="Visible">Visible</option>
                                <option value="Oculto">Oculto</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center">
                            <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Curso:</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    Seleccionar...
                                </button>
                                <ul class="dropdown-menu px-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($cursos_disponibles as $c)
                                        <li>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input filter-curso-chk" type="checkbox" value="{{ $c }}" id="chkCur_{{ $loop->index }}">
                                                <label class="form-check-label text-sm mb-0" for="chkCur_{{ $loop->index }}">{{ $c }}</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
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
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Problema</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Cursos y Categorías</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Lenguajes</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Estado & LLM</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Disponibilidad</th>
                                    @canany(['editar problemas', 'eliminar problemas', 'ver informe del problema'])
                                        <th class="none text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($problemas as $problema)
                                    <tr>
                                        <td>
                                            <div class="d-inline-flex align-items-center py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $problema->nombre }}</h6>
                                                    <p class="text-xs text-secondary mb-0 mt-1">Código: <span class="badge border border-secondary text-secondary text-xxs px-2 py-0">{{ $problema->codigo }}</span></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2 py-1">
                                                <div class="d-flex flex-wrap align-items-center gap-1">
                                                    <span class="text-xxs text-muted text-uppercase font-weight-bold me-1">Cursos:</span>
                                                    @foreach ($problema->cursos()->get() as $curso)
                                                        <span class="badge bg-gradient-primary text-xxs">{{ $curso->codigo }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex flex-wrap align-items-center gap-1">
                                                    <span class="text-xxs text-muted text-uppercase font-weight-bold me-1">Categorías:</span>
                                                    @foreach ($problema->categorias()->get() as $categoria)
                                                        <span class="badge bg-gradient-info text-xxs">{{ $categoria->nombre }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $lenguaje)
                                                    <span class="badge bg-gradient-secondary text-xxs">{{ $lenguaje }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge {{ $problema->visible == true ? 'bg-gradient-success' : 'bg-gradient-secondary' }} text-xxs">
                                                    <i class="fa {{ $problema->visible == true ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i> {{ $problema->visible == true ? 'Visible' : 'Oculto' }}
                                                </span>
                                                <span class="badge {{ $problema->habilitar_llm == true ? 'bg-gradient-info' : 'bg-gradient-secondary' }} text-xxs">
                                                    <i class="fa fa-robot me-1"></i> LLM: {{ $problema->habilitar_llm == true ? 'Activo (máx ' . $problema->limite_llm . ')' : 'Inactivo' }}
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
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-2">
                                                    @can('ver informe del problema')
                                                        <a class="btn btn-sm btn-outline-primary mb-0" title="Ver Informe"
                                                        href="{{ route('informes.problemas.index', ['id' => $problema->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                                    @endcan
                                                    @can('editar problemas')
                                                        <a class="btn btn-sm btn-outline-info mb-0" title="Casos de Prueba"
                                                            href="{{ route('casos_pruebas.assign', ['id' => $problema->id]) }}"><i class="fa fa-vials me-1"></i> Casos</a>
                                                        <a class="btn btn-sm btn-outline-secondary mb-0" title="Configuración LLM"
                                                            href="{{ route('problemas.editar_config_llm', ['id' => $problema->id]) }}"><i class="fa fa-robot me-1"></i> LLM</a>
                                                        <a class="btn btn-sm btn-outline-dark mb-0" title="Editorial"
                                                            href="{{ route('problemas.editorial', ['id' => $problema->id]) }}"><i class="fa fa-book me-1"></i> Editorial</a>
                                                        <a class="btn btn-sm btn-outline-warning mb-0" title="Editar Problema"
                                                            href="{{ route('problemas.editar', ['id' => $problema->id]) }}"><i class="fa fa-pencil me-1"></i> Editar</a>
                                                    @endcan
                                                    @can('eliminar problemas')
                                                        <form action="{{ route('problemas.eliminar', ['id' => $problema->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el problema '.$problema->nombre}}', {{$problema->id}})" id="eliminarForm_{{$problema->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-0" title="Eliminar Problema"><i class="fa fa-fw fa-trash me-1"></i> Eliminar</button>
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
                    
                    // Integrar filtros visualmente al lado del selector de cantidad
                    $('#custom-filters-src').children().appendTo('.custom-filters-container');
                    $('#custom-filters-src').remove();
                    
                    $('#filterEstado').on('change', function() {
                        table.column(3).search(this.value).draw();
                    });
                    
                    function updateCol1Filters() {
                        var cursos = $('.filter-curso-chk:checked').map(function() { return this.value; }).get();
                        var categorias = $('.filter-categoria-chk:checked').map(function() { return this.value; }).get();
                        
                        var searchStr = '';
                        
                        var cursoRegex = cursos.length > 0 ? '(' + cursos.join('|') + ')' : '';
                        var catRegex = categorias.length > 0 ? '(' + categorias.join('|') + ')' : '';
                        
                        if (cursoRegex && catRegex) {
                            searchStr = '(?=.*' + cursoRegex + ')(?=.*' + catRegex + ')';
                        } else if (cursoRegex) {
                            searchStr = cursoRegex;
                        } else if (catRegex) {
                            searchStr = catRegex;
                        }
                        
                        table.column(1).search(searchStr, true, false).draw();
                    }
                    
                    $('.filter-curso-chk, .filter-categoria-chk').on('change', updateCol1Filters);
                }
            }, 500);
        });
    </script>
@endpush
