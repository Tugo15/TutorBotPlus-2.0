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
                            <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center">
                                <i class="fa fa-users text-success text-sm me-2"></i>
                                <span>Usuarios Conectados Ahora</span>
                                <span class="badge bg-success-soft text-success border border-success ms-2 font-weight-bold" id="badgeTotalConectados">
                                    <span class="pulse-online me-1"></span>{{ $cantidad_conectados }}
                                </span>
                            </h6>
                            <p class="text-xs text-secondary mb-0">Sesiones activas en la plataforma pertenecientes a los cursos asignados.</p>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <!-- Búsqueda rápida -->
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text bg-white border-end-0 py-1 pe-1 text-secondary">
                                    <i class="fa fa-search text-xs"></i>
                                </span>
                                <input type="text" id="filtroBuscarConectados" class="form-control border-start-0 text-xs py-1 ps-1" placeholder="Buscar nombre, RUT o IP..." autocomplete="off">
                            </div>
                            <!-- Filtro por Rol -->
                            <select id="filtroRolConectados" class="form-select form-select-sm text-xs py-1" style="width: 140px;">
                                <option value="">Todos los roles</option>
                                <option value="estudiante">Estudiantes</option>
                                <option value="profesor">Profesores</option>
                                <option value="administrador">Administradores</option>
                            </select>
                            <!-- Paginación / Cantidad visible -->
                            <select id="filtroPaginacionConectados" class="form-select form-select-sm text-xs py-1" style="width: 115px;">
                                <option value="10" selected>10 por pág.</option>
                                <option value="25">25 por pág.</option>
                                <option value="50">50 por pág.</option>
                                <option value="todos">Ver todos</option>
                            </select>
                            @can('ver usuario')
                                <a href="{{ route('usuarios.index') }}" class="btn btn-xs btn-outline-secondary mb-0">
                                    <i class="ni ni-single-02 text-dark me-1"></i> Gestionar
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive custom-scroll" style="max-height: 440px; overflow-y: auto;">
                            <table class="table align-items-center mb-0 table-hover table-sticky-header" id="tablaUsuariosConectados">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="min-width: 220px;">Usuario</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="min-width: 140px;">Rol</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="min-width: 140px;">Dirección IP</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="min-width: 150px;">Actividad</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyUsuariosConectados">
                                    @forelse($usuarios_conectados as $uCon)
                                        @php
                                            $esTu = ($uCon->id === auth()->id());
                                            $iniciales = mb_strtoupper(mb_substr($uCon->firstname ?? '', 0, 1) . mb_substr($uCon->lastname ?? '', 0, 1));
                                            $rolLower = mb_strtolower($uCon->roles_str ?? 'estudiante');
                                        @endphp
                                        <tr class="fila-usuario-conectado"
                                            data-nombre="{{ mb_strtolower($uCon->firstname . ' ' . $uCon->lastname) }}"
                                            data-rut="{{ mb_strtolower($uCon->rut ?? '') }}"
                                            data-ip="{{ $uCon->ip ?? '' }}"
                                            data-rol="{{ $rolLower }}">
                                            <td class="ps-3 py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="position-relative me-2 flex-shrink-0">
                                                        <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center text-xxs font-weight-bold {{ $esTu ? 'bg-gradient-success text-white' : 'bg-light text-dark border' }}" style="width: 32px; height: 32px;">
                                                            {{ $iniciales ?: 'U' }}
                                                        </div>
                                                        <span class="pulse-online position-absolute bottom-0 end-0 border border-white" style="margin-bottom: -1px; margin-right: -1px;"></span>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <span class="text-xs font-weight-bold text-dark mb-0">
                                                            {{ $uCon->firstname }} {{ $uCon->lastname }}
                                                            @if($esTu)
                                                                <span class="badge bg-success-soft text-success text-xxs border border-success ms-1 py-0 px-1">Tú</span>
                                                            @endif
                                                        </span>
                                                        <span class="text-xxs text-secondary font-monospace">{{ $uCon->rut }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center py-2">
                                                @if(str_contains($rolLower, 'admin'))
                                                    <span class="badge bg-dark text-white text-xxs px-2 py-1">
                                                        <i class="fa fa-shield-alt me-1"></i>{{ $uCon->roles_str }}
                                                    </span>
                                                @elseif(str_contains($rolLower, 'profesor') || str_contains($rolLower, 'docente'))
                                                    <span class="badge bg-warning text-dark text-xxs px-2 py-1">
                                                        <i class="fa fa-chalkboard-teacher me-1"></i>{{ $uCon->roles_str }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-secondary border text-xxs px-2 py-1">
                                                        <i class="fa fa-user-graduate me-1"></i>{{ $uCon->roles_str ?: 'Estudiante' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge border border-info text-info bg-white text-xxs px-2 py-1 font-monospace">
                                                    <i class="fa fa-network-wired me-1"></i>{{ $uCon->ip }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3 py-2">
                                                @if($esTu)
                                                    <span class="badge bg-success text-white text-xxs px-2 py-1">
                                                        <i class="fa fa-check-circle me-1"></i>Activo ahora
                                                    </span>
                                                @else
                                                    <span class="text-xxs text-secondary">
                                                        <i class="far fa-clock me-1"></i>{{ $uCon->ultima_actividad }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="filaSinConectados">
                                            <td colspan="4" class="text-center py-4 text-xs text-secondary">
                                                <i class="fa fa-user-slash text-secondary fa-2x mb-2 d-block opacity-4"></i>
                                                Sin usuarios conectados en este momento.
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr id="filaSinResultadosFiltro" style="display: none;">
                                        <td colspan="4" class="text-center py-4 text-xs text-secondary">
                                            <i class="fa fa-search text-secondary fa-2x mb-2 d-block opacity-4"></i>
                                            No se encontraron usuarios conectados que coincidan con los filtros aplicados.
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-xs btn-outline-secondary mb-0" id="btnLimpiarFiltrosConectados">
                                                    <i class="fa fa-undo me-1"></i> Restablecer filtros
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if(count($usuarios_conectados) > 0)
                        <div class="card-footer py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" id="footerConectados">
                            <div class="text-xxs text-secondary">
                                Mostrando <strong id="infoDesdeConectados" class="text-dark">1</strong>-<strong id="infoHastaConectados" class="text-dark">{{ min(10, count($usuarios_conectados)) }}</strong> de <strong id="infoTotalConectados" class="text-dark">{{ count($usuarios_conectados) }}</strong> usuarios conectados
                            </div>
                            <div class="d-flex align-items-center gap-1" id="paginacionConectados">
                                <!-- Botones de paginación generados dinámicamente -->
                            </div>
                        </div>
                    @endif
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

@push('css')
<style>
    .pulse-online {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #2dce89;
        box-shadow: 0 0 0 0 rgba(45, 206, 137, 0.7);
        animation: pulse-green 1.8s infinite;
        vertical-align: middle;
    }
    @keyframes pulse-green {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(45, 206, 137, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(45, 206, 137, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(45, 206, 137, 0);
        }
    }
    .table-sticky-header thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #e9ecef !important;
    }
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    .bg-success-soft {
        background-color: rgba(45, 206, 137, 0.15) !important;
    }
</style>
@endpush

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

        // Control y filtrado de Usuarios Conectados
        (function() {
            const inputBuscar = document.getElementById('filtroBuscarConectados');
            const selectRol = document.getElementById('filtroRolConectados');
            const selectPaginacion = document.getElementById('filtroPaginacionConectados');
            const btnLimpiar = document.getElementById('btnLimpiarFiltrosConectados');
            const filas = Array.from(document.querySelectorAll('.fila-usuario-conectado'));
            const filaSinResultados = document.getElementById('filaSinResultadosFiltro');
            const infoDesde = document.getElementById('infoDesdeConectados');
            const infoHasta = document.getElementById('infoHastaConectados');
            const infoTotal = document.getElementById('infoTotalConectados');
            const contenedorPaginacion = document.getElementById('paginacionConectados');

            if (!filas.length) return;

            let paginaActual = 1;

            function aplicarFiltros() {
                const termino = (inputBuscar ? inputBuscar.value : '').toLowerCase().trim();
                const rol = (selectRol ? selectRol.value : '').toLowerCase().trim();
                const porPaginaVal = selectPaginacion ? selectPaginacion.value : '10';
                const porPagina = porPaginaVal === 'todos' ? Infinity : parseInt(porPaginaVal, 10);

                const filasFiltradas = [];

                filas.forEach(fila => {
                    const nombre = fila.getAttribute('data-nombre') || '';
                    const rut = fila.getAttribute('data-rut') || '';
                    const ip = fila.getAttribute('data-ip') || '';
                    const filaRol = fila.getAttribute('data-rol') || '';

                    const coincideTexto = !termino || nombre.includes(termino) || rut.includes(termino) || ip.includes(termino);
                    const coincideRol = !rol || filaRol.includes(rol);

                    if (coincideTexto && coincideRol) {
                        filasFiltradas.push(fila);
                    } else {
                        fila.style.display = 'none';
                    }
                });

                const totalFiltrados = filasFiltradas.length;
                const totalPaginas = porPagina === Infinity ? 1 : Math.max(1, Math.ceil(totalFiltrados / porPagina));

                if (paginaActual > totalPaginas) {
                    paginaActual = totalPaginas;
                }
                if (paginaActual < 1) {
                    paginaActual = 1;
                }

                // Mostrar mensaje de no encontrados si corresponde
                if (filaSinResultados) {
                    filaSinResultados.style.display = (totalFiltrados === 0 && filas.length > 0) ? '' : 'none';
                }

                // Paginar filas filtradas
                const inicio = porPagina === Infinity ? 0 : (paginaActual - 1) * porPagina;
                const fin = porPagina === Infinity ? totalFiltrados : inicio + porPagina;

                filasFiltradas.forEach((fila, index) => {
                    if (index >= inicio && index < fin) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });

                // Actualizar info textual
                if (infoDesde && infoHasta && infoTotal) {
                    if (totalFiltrados === 0) {
                        infoDesde.textContent = '0';
                        infoHasta.textContent = '0';
                    } else {
                        infoDesde.textContent = (inicio + 1).toString();
                        infoHasta.textContent = Math.min(fin, totalFiltrados).toString();
                    }
                    infoTotal.textContent = totalFiltrados.toString();
                }

                renderPaginacion(totalPaginas, totalFiltrados, porPagina);
            }

            function renderPaginacion(totalPaginas, totalFiltrados, porPagina) {
                if (!contenedorPaginacion) return;
                contenedorPaginacion.innerHTML = '';

                if (porPagina === Infinity || totalPaginas <= 1) {
                    return;
                }

                // Botón Anterior
                const btnPrev = document.createElement('button');
                btnPrev.type = 'button';
                btnPrev.className = 'btn btn-xs ' + (paginaActual === 1 ? 'btn-outline-secondary disabled' : 'btn-outline-secondary') + ' mb-0 px-2 py-1';
                btnPrev.innerHTML = '<i class="fa fa-chevron-left text-xxs"></i>';
                btnPrev.disabled = (paginaActual === 1);
                btnPrev.addEventListener('click', function() {
                    if (paginaActual > 1) {
                        paginaActual--;
                        aplicarFiltros();
                    }
                });
                contenedorPaginacion.appendChild(btnPrev);

                // Botones de Páginas
                let startPage = Math.max(1, paginaActual - 2);
                let endPage = Math.min(totalPaginas, startPage + 4);
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let p = startPage; p <= endPage; p++) {
                    const btnPag = document.createElement('button');
                    btnPag.type = 'button';
                    btnPag.className = 'btn btn-xs ' + (p === paginaActual ? 'btn-dark' : 'btn-outline-secondary') + ' mb-0 px-2 py-1';
                    btnPag.textContent = p;
                    btnPag.addEventListener('click', (function(page) {
                        return function() {
                            paginaActual = page;
                            aplicarFiltros();
                        };
                    })(p));
                    contenedorPaginacion.appendChild(btnPag);
                }

                // Botón Siguiente
                const btnNext = document.createElement('button');
                btnNext.type = 'button';
                btnNext.className = 'btn btn-xs ' + (paginaActual === totalPaginas ? 'btn-outline-secondary disabled' : 'btn-outline-secondary') + ' mb-0 px-2 py-1';
                btnNext.innerHTML = '<i class="fa fa-chevron-right text-xxs"></i>';
                btnNext.disabled = (paginaActual === totalPaginas);
                btnNext.addEventListener('click', function() {
                    if (paginaActual < totalPaginas) {
                        paginaActual++;
                        aplicarFiltros();
                    }
                });
                contenedorPaginacion.appendChild(btnNext);
            }

            if (inputBuscar) {
                inputBuscar.addEventListener('keyup', function() {
                    paginaActual = 1;
                    aplicarFiltros();
                });
            }

            if (selectRol) {
                selectRol.addEventListener('change', function() {
                    paginaActual = 1;
                    aplicarFiltros();
                });
            }

            if (selectPaginacion) {
                selectPaginacion.addEventListener('change', function() {
                    paginaActual = 1;
                    aplicarFiltros();
                });
            }

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function() {
                    if (inputBuscar) inputBuscar.value = '';
                    if (selectRol) selectRol.value = '';
                    paginaActual = 1;
                    aplicarFiltros();
                });
            }

            aplicarFiltros();
        })();
    });
</script>
@endpush

