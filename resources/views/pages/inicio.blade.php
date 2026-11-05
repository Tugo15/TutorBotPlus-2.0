@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Inicio'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Inicio'])

    <div class="container-fluid py-4">
        <!-- Encabezado simple y filtro por curso -->
        <div class="card shadow-xs border mb-4">
            <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm bg-gradient-dark me-3 rounded-circle d-flex align-items-center justify-content-center shadow-xs">
                        <i class="ni ni-tv-2 text-white text-xs"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark">
                            Bienvenido, {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                            @if(isset($curso_activo) && $curso_activo)
                                Filtrando por: <strong class="text-dark">{{ $curso_activo->nombre }}</strong>
                                <span class="badge border border-secondary text-secondary font-monospace text-xxs ms-1">{{ $curso_activo->codigo }}</span>
                            @else
                                Mostrando métricas consolidadas de <strong>todos tus cursos</strong> ({{ isset($misCursos) ? count($misCursos) : 0 }})
                            @endif
                        </p>
                    </div>
                </div>
                @if(isset($misCursos) && count($misCursos) > 1)
                    @if(count($misCursos) <= 3)
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <a href="{{ route('home') }}"
                               class="btn btn-xs {{ !$id_curso_seleccionado ? 'btn-dark' : 'btn-outline-secondary' }} mb-0">
                                <i class="fa fa-layer-group text-primary me-1"></i> Todos mis cursos
                            </a>
                            @foreach($misCursos as $cItem)
                                <a href="{{ route('home', ['id_curso' => $cItem->id]) }}"
                                   class="btn btn-xs {{ $id_curso_seleccionado == $cItem->id ? 'btn-dark' : 'btn-outline-secondary' }} mb-0"
                                   title="{{ $cItem->nombre }} ({{ $cItem->codigo }})">
                                    <i class="fa fa-graduation-cap text-warning me-1"></i> {{ $cItem->nombre }} <span class="opacity-7 ms-1">({{ $cItem->codigo }})</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <div class="dropdown">
                                <button class="btn btn-sm {{ $id_curso_seleccionado ? 'btn-dark' : 'btn-outline-dark' }} dropdown-toggle mb-0 d-flex align-items-center shadow-xs" 
                                        type="button" id="dropdownFiltroCurso" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                    <i class="fa fa-graduation-cap text-warning me-2"></i>
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;">
                                        {{ $curso_activo ? $curso_activo->nombre : 'Todos mis cursos (' . count($misCursos) . ')' }}
                                    </span>
                                    @if($curso_activo)
                                        <span class="badge bg-white text-dark text-xxs ms-2 font-monospace">{{ $curso_activo->codigo }}</span>
                                    @endif
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border py-2" aria-labelledby="dropdownFiltroCurso" style="max-height: 380px; overflow-y: auto; min-width: 330px;" id="menuFiltroCursos">
                                    <li class="px-3 py-1 text-xxs font-weight-bolder text-uppercase text-secondary">
                                        Filtrar por curso
                                    </li>
                                    @if(count($misCursos) > 5)
                                        <li class="px-3 py-1 mb-1">
                                            <input type="text" class="form-control form-control-sm text-xs" id="buscarCursoInput" placeholder="Buscar curso o código..." autocomplete="off">
                                        </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ !$id_curso_seleccionado ? 'active fw-bold' : '' }}" 
                                           href="{{ route('home') }}">
                                            <span class="d-flex align-items-center">
                                                <i class="fa fa-layer-group {{ !$id_curso_seleccionado ? 'text-white' : 'text-primary' }} me-2"></i>
                                                <span>Todos mis cursos</span>
                                            </span>
                                            <span class="badge {{ !$id_curso_seleccionado ? 'bg-white text-dark' : 'bg-light text-secondary' }}">
                                                {{ count($misCursos) }}
                                            </span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    @foreach($misCursos as $cItem)
                                        <li class="item-curso-li">
                                            <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ $id_curso_seleccionado == $cItem->id ? 'active fw-bold' : '' }}" 
                                               href="{{ route('home', ['id_curso' => $cItem->id]) }}"
                                               title="{{ $cItem->nombre }} ({{ $cItem->codigo }})">
                                                <span class="d-flex align-items-center text-truncate me-2" style="max-width: 230px;">
                                                    <i class="fa fa-graduation-cap {{ $id_curso_seleccionado == $cItem->id ? 'text-warning' : 'text-secondary opacity-7' }} me-2"></i>
                                                    <span class="text-truncate">{{ $cItem->nombre }}</span>
                                                </span>
                                                <span class="badge {{ $id_curso_seleccionado == $cItem->id ? 'bg-white text-dark' : 'border text-secondary' }} font-monospace text-xxs">
                                                    {{ $cItem->codigo }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @if($id_curso_seleccionado)
                                <a href="{{ route('home') }}" class="btn btn-xs btn-outline-secondary mb-0 d-flex align-items-center" title="Quitar filtro de curso">
                                    <i class="fa fa-times me-1"></i> Quitar filtro
                                </a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- 4 Tarjetas simples (mismo estilo que Informes + íconos del Sidebar) -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-xs border h-100">
                    <div class="card-body p-3">
                        <p class="text-xs mb-1 text-uppercase font-weight-bold text-secondary">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10 me-1"></i> Conectados Ahora
                        </p>
                        <h5 class="font-weight-bolder text-dark mb-0">
                            {{ $cantidad_conectados }} <span class="text-xs font-weight-normal text-secondary">/ {{ $total_usuarios }} usuarios</span>
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-xs border h-100">
                    <div class="card-body p-3">
                        <p class="text-xs mb-1 text-uppercase font-weight-bold text-secondary">
                            <i class="fa fa-code text-danger text-sm opacity-10 me-1"></i> Envíos Correctos / Incorrectos
                        </p>
                        <h5 class="font-weight-bolder mb-0">
                            <span class="text-success">{{ $envios_correctos }}</span>
                            <span class="text-secondary mx-1">/</span>
                            <span class="text-danger">{{ $envios_incorrectos }}</span>
                            <span class="text-xs font-weight-normal text-secondary ms-1">({{ $total_envios }} total)</span>
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-xs border h-100">
                    <div class="card-body p-3">
                        <p class="text-xs mb-1 text-uppercase font-weight-bold text-secondary">
                            <i class="fa fa-calendar-check text-info text-sm opacity-10 me-1"></i> Tasa de Éxito
                        </p>
                        <h5 class="font-weight-bolder text-dark mb-0">{{ $tasa_exito_global }}%</h5>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-xs border h-100">
                    <div class="card-body p-3">
                        <p class="text-xs mb-1 text-uppercase font-weight-bold text-secondary">
                            <i class="fa fa-list-alt text-success text-sm opacity-10 me-1"></i> Problemas Visibles
                        </p>
                        <h5 class="font-weight-bolder text-dark mb-0">
                            {{ $problemas_visibles }} <span class="text-xs font-weight-normal text-secondary">/ {{ $total_problemas }}</span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios Conectados Ahora (Ancho completo) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-xs border">
                    <div class="card-header pb-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark">
                                <i class="ni ni-single-02 text-dark text-sm opacity-10 me-2"></i>Usuarios Conectados Ahora ({{ $cantidad_conectados }})
                            </h6>
                            <p class="text-xs text-secondary mb-0">Sesiones activas en la plataforma pertenecientes a los cursos asignados.</p>
                        </div>
                        @can('ver usuario')
                            <a href="{{ route('usuarios.index') }}" class="btn btn-xs btn-outline-secondary mb-0">
                                <i class="ni ni-single-02 text-dark me-1"></i> Gestionar Usuarios
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Usuario</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Rol</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Dirección IP</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Actividad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usuarios_conectados as $uCon)
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <span class="text-xs font-weight-bold text-dark d-block">{{ $uCon->firstname }} {{ $uCon->lastname }}</span>
                                                <span class="text-xxs text-secondary">{{ $uCon->rut }}</span>
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge border border-secondary text-secondary bg-white text-xxs">{{ $uCon->roles_str }}</span>
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge border border-info text-info bg-white text-xxs px-2 py-1">
                                                    <i class="fa fa-network-wired me-1"></i>{{ $uCon->ip }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3 py-2 text-xxs text-secondary">
                                                {{ $uCon->ultima_actividad }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-xs text-secondary">Sin usuarios conectados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mis Cursos con accesos directos (Ancho completo) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-xs border">
                    <div class="card-header pb-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark">
                                <i class="fa fa-graduation-cap text-warning text-sm opacity-10 me-2"></i>Mis Cursos
                            </h6>
                            <p class="text-xs text-secondary mb-0">Asignaturas a las que perteneces y accesos rápidos a sus contenidos.</p>
                        </div>
                        @can('ver curso')
                            <a href="{{ route('cursos.index') }}" class="btn btn-xs btn-outline-secondary mb-0">
                                <i class="fa fa-graduation-cap text-warning me-1"></i> Gestionar Cursos
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Nombre del Curso</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Código</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Problemas</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Resueltos / Intentos</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumen_cursos as $c)
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-folder text-warning me-2 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $c->nombre }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge border border-primary text-primary text-xxs px-2.5 py-1 bg-white">{{ $c->codigo }}</span>
                                            </td>
                                            <td class="text-center py-2 text-xs font-weight-bold">
                                                {{ $c->cantidad_problemas }}
                                            </td>
                                            <td class="text-center py-2 text-xs">
                                                <span class="text-success font-weight-bold">{{ $c->cantidad_resueltos }}</span> / {{ $c->cantidad_intentos }}
                                            </td>
                                            <td class="text-center py-2">
                                                <a href="{{ route('cursos.estudiantes', ['id' => $c->id]) }}" class="btn btn-xs btn-outline-info mb-0 me-1" title="Gestionar Estudiantes">
                                                    <i class="fa fa-users me-1"></i> Estudiantes
                                                </a>
                                                <a href="{{ route('problemas.index', ['id_curso' => $c->id]) }}" class="btn btn-xs btn-outline-primary mb-0 me-1" title="Gestionar Ejercicios">
                                                    <i class="fa fa-list-alt text-success me-1"></i> Problemas
                                                </a>
                                                <a href="{{ route('certamen.index', ['id_curso' => $c->id]) }}" class="btn btn-xs btn-outline-warning mb-0 me-1">
                                                    <i class="fa fa-calendar-check text-warning me-1"></i> Evaluaciones
                                                </a>
                                                <a href="{{ route('informe.curso', ['id_curso' => $c->id]) }}" class="btn btn-xs btn-outline-secondary mb-0">
                                                    <i class="fa fa-chart-bar text-primary me-1"></i> Informe
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-xs text-secondary">Sin cursos asignados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Envíos en Mis Cursos (Ancho completo) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-xs border">
                    <div class="card-header pb-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark">
                                <i class="fa fa-code text-danger text-sm opacity-10 me-2"></i>Últimos Envíos
                            </h6>
                            <p class="text-xs text-secondary mb-0">Envíos de soluciones recientes en las asignaturas asignadas.</p>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Estudiante</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Problema</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Curso</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Lenguaje</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">IP Origen</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Fecha</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ultimos_envios as $uEnv)
                                        @php
                                            $esAceptado = (bool) $uEnv->solucionado;
                                            $esError = in_array($uEnv->estado, ['Rechazado', 'Error']);
                                        @endphp
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <span class="text-xs font-weight-bold text-dark d-block">{{ $uEnv->firstname }} {{ $uEnv->lastname }}</span>
                                                <span class="text-xxs text-secondary">{{ $uEnv->rut }}</span>
                                            </td>
                                            <td class="py-2 text-xs font-weight-bold text-dark">
                                                {{ $uEnv->nombre_problema }}
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="text-xs font-weight-bold text-dark d-block">{{ $uEnv->nombre_curso }}</span>
                                                <span class="badge border border-primary text-primary bg-white text-xxs">{{ $uEnv->codigo_curso }}</span>
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge border border-dark text-dark bg-white text-xxs">{{ $uEnv->nombre_lenguaje }}</span>
                                            </td>
                                            <td class="text-center py-2">
                                                @if(!empty($uEnv->ip_origen))
                                                    <span class="badge border border-info text-info bg-white text-xxs px-2 py-0.5">
                                                        <i class="fa fa-network-wired me-1"></i>{{ $uEnv->ip_origen }}
                                                    </span>
                                                @else
                                                    <span class="text-xxs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center py-2">
                                                @if($esAceptado)
                                                    <span class="badge bg-success text-xxs">
                                                        {{ !empty($uEnv->variacion_formato) ? 'Aceptado (Variación de Formato)' : 'Aceptado' }}
                                                    </span>
                                                @elseif($esError)
                                                    <span class="badge bg-danger text-xxs">
                                                        {{ $uEnv->resultado ?? $uEnv->estado ?? 'Rechazado' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-xxs">En Proceso</span>
                                                @endif
                                            </td>
                                            <td class="text-center py-2 text-xs text-secondary">
                                                {{ $uEnv->created_at ? \Carbon\Carbon::parse($uEnv->created_at)->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td class="text-center py-2">
                                                @if(isset($uEnv->token))
                                                    <a href="{{ route('envios.ver', ['token' => $uEnv->token]) }}" class="btn btn-xs btn-outline-info mb-0">Ver</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-3 text-xs text-secondary">Sin envíos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
        const inputBuscar = document.getElementById('buscarCursoInput');
        if (inputBuscar) {
            inputBuscar.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            inputBuscar.addEventListener('keyup', function(e) {
                e.stopPropagation();
                const term = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('#menuFiltroCursos .item-curso-li');
                items.forEach(function(item) {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush

