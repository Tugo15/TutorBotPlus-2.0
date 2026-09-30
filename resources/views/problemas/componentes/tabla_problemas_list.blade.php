@php
    $categorias_disponibles = collect();
    $cursos_disponibles = collect();
    foreach($problemas as $prob) {
        if(isset($prob->categorias)) {
            foreach($prob->categorias as $cat) { $categorias_disponibles->put($cat->nombre, $cat->nombre); }
        }
        if(isset($prob->cursos)) {
            foreach($prob->cursos as $cur) { $cursos_disponibles->put($cur->codigo, ['codigo' => $cur->codigo, 'nombre' => $cur->nombre]); }
        }
    }
    $categorias_disponibles = $categorias_disponibles->sort();
    $cursos_disponibles = $cursos_disponibles->sortBy('codigo');
@endphp

<style>
    .custom-filters-container,
    .dataTables_filter {
        display: flex !important;
        align-items: center !important;
        height: 32px !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        overflow: visible !important;
    }
    .custom-filters-container {
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
        white-space: nowrap !important;
    }
    .dataTables_filter {
        justify-content: flex-end !important;
    }
    .custom-filters-container label,
    .dataTables_filter label {
        display: inline-flex !important;
        align-items: center !important;
        height: 32px !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #6c757d !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }
    .custom-filters-container select,
    .custom-filters-container .dropdown-toggle,
    .dataTables_filter input {
        height: 32px !important;
        max-height: 32px !important;
        min-height: 32px !important;
        line-height: 1.2 !important;
        padding: 0.25rem 0.5rem !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        font-size: 0.75rem !important;
        box-sizing: border-box !important;
        border-radius: 0.375rem !important;
        vertical-align: middle !important;
    }
    .dataTables_filter input {
        margin-left: 0.35rem !important;
    }
    .custom-filters-container .dropdown {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        height: 32px !important;
    }
    .custom-filters-container .dropdown-menu {
        z-index: 1060 !important;
    }
</style>

<div id="custom-filters-src" class="d-none">
    @if(isset($mostrar_curso) && $mostrar_curso && count($cursos_disponibles) > 0)
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Curso:</label>
            <div class="dropdown">
                <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0 d-inline-flex align-items-center justify-content-between text-xs" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" data-bs-auto-close="outside" style="height: 32px; min-width: 130px;">
                    <span class="lbl-curso-selected">Seleccionar...</span>
                </button>
                <ul class="dropdown-menu px-2" style="max-height: 200px; overflow-y: auto;">
                    @foreach($cursos_disponibles as $cItem)
                        <li>
                            <div class="form-check mb-1">
                                <input class="form-check-input filter-curso-chk" type="checkbox" value="{{ $cItem['codigo'] }}" id="chkCur_{{ $loop->index }}">
                                <label class="form-check-label text-sm mb-0" for="chkCur_{{ $loop->index }}">{{ $cItem['codigo'] }} - {{ $cItem['nombre'] }}</label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="d-flex align-items-center">
        <label for="filterEstado" class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Estado:</label>
        <select id="filterEstado" class="form-select form-select-sm w-auto border-secondary" style="min-width: 110px; height: 32px;">
            <option value="">Todos</option>
            <option value="Visible">Visible</option>
            <option value="Oculto">Oculto</option>
        </select>
    </div>

    <div class="d-flex align-items-center">
        <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Categoría:</label>
        <div class="dropdown">
            <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0 d-inline-flex align-items-center justify-content-between text-xs" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" data-bs-auto-close="outside" style="height: 32px; min-width: 130px;">
                <span class="lbl-cat-selected">Seleccionar...</span>
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

<div class="table-responsive p-0 mt-3">
    <table class="table align-items-center mb-0" id="table" style="width:100%">
        <thead>
            <tr class="border-bottom">
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Problema</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Curso(s)</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Categorías y Dificultad</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Lenguajes</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Estado</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Disponibilidad</th>
                @canany(['editar problemas', 'eliminar problemas', 'ver informe del problema'])
                    <th class="none text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Acciones</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($problemas as $problema)
                <tr>
                    <td class="align-middle text-center">
                        <div class="d-inline-flex px-2 py-1 justify-content-center text-center align-items-center">
                            <div class="d-flex flex-column justify-content-center text-start">
                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $problema->nombre }}</h6>
                                <p class="text-xs text-secondary mb-0 mt-1">Código: <span class="badge border border-secondary text-secondary text-xxs px-2 py-0 bg-white">{{ $problema->codigo }}</span></p>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-wrap gap-1 py-1 justify-content-center">
                            @if(isset($problema->cursos) && count($problema->cursos) > 0)
                                @foreach ($problema->cursos as $c)
                                    <span class="badge border border-primary text-primary text-xxs bg-white" title="{{ $c->nombre }}">{{ $c->codigo }}</span>
                                @endforeach
                            @else
                                <span class="badge border border-secondary text-secondary text-xxs bg-white">Sin Curso</span>
                            @endif
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-column gap-1 py-1 justify-content-center">
                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                @php
                                    $difProbClass = ($problema->dificultad == 'Fácil') ? 'border-success text-success' : (($problema->dificultad == 'Difícil') ? 'border-danger text-danger' : 'border-warning text-warning');
                                @endphp
                                <span class="badge border {{ $difProbClass }} text-xxs bg-white">{{ $problema->dificultad ?? 'Medio' }}</span>
                                @if(isset($problema->categorias))
                                    @foreach ($problema->categorias as $categoria)
                                        <span class="badge border border-danger text-danger text-xxs bg-white">{{ $categoria->nombre }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            @if(isset($problema->lenguajes))
                                @foreach ($problema->lenguajes->pluck('abreviatura')->unique() as $lenguaje)
                                    <span class="badge border border-dark text-dark text-xxs bg-white">{{ $lenguaje }}</span>
                                @endforeach
                            @endif
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-column align-items-center justify-content-center gap-1">
                            <span class="badge border {{ $problema->visible == true ? 'border-success text-success' : 'border-secondary text-secondary' }} text-xxs bg-white">
                                <i class="fa {{ $problema->visible == true ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i> {{ $problema->visible == true ? 'Visible' : 'Oculto' }}
                            </span>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex flex-column justify-content-center text-center">
                            <span class="text-xs font-weight-bold text-dark">Inicio: {{ $problema->fecha_inicio ? $problema->fecha_inicio : 'Inmediata' }}</span>
                            <span class="text-xs text-secondary">Término: {{ $problema->fecha_termino ? $problema->fecha_termino : 'Indefinida' }}</span>
                        </div>
                    </td>
                    @canany(['editar problemas', 'eliminar problemas', 'ver informe del problema'])
                        <td class="align-middle text-center">
                            <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1 flex-wrap">
                                @can('ver informe del problema')
                                    @if(isset($id_curso_activo) && $id_curso_activo)
                                        <a class="btn btn-xs btn-outline-primary mb-0" title="Ver Informe del Curso"
                                            href="{{ route('informe.problema', ['id_curso' => $id_curso_activo, 'id_problema' => $problema->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                    @else
                                        <a class="btn btn-xs btn-outline-primary mb-0" title="Ver Informe (Seleccionar Curso o Todos)"
                                            href="{{ route('informes.problemas.index', ['id' => $problema->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                    @endif
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
