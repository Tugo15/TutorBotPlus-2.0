@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Cursos'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Cursos'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-graduation-cap me-2 text-success"></i>Gestión de Cursos</h6>
                            <p class="text-xs text-secondary mb-0">Administre las asignaturas y secciones disponibles en la plataforma.</p>
                        </div>
                        @can('crear curso')
                        <a class="btn btn-sm btn-dark mb-0" href="{{ route('cursos.crear') }}"><i class="fa fa-plus me-1"></i> Crear Curso</a>
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
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr class="border-bottom">
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Nombre</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Código</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Creado</th>
                                    @canany(['editar curso', 'eliminar curso', 'ver informe del curso'])
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cursos as $curso)
                                    <tr>
                                        <td>
                                            <div class="d-inline-flex align-items-center py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $curso->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="badge border border-primary text-primary text-xxs px-2.5 py-1 bg-white">{{ $curso->codigo }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-xs text-secondary font-weight-bold mb-0">
                                                {{ $curso->fecha ? $curso->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar curso', 'eliminar curso', 'ver informe del curso'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                                    @can('ver informe del curso')
                                                        <a class="btn btn-xs btn-outline-primary mb-0" title="Ver Informe del Curso"
                                                            href="{{ route('informe.curso', ['id_curso' => $curso->id]) }}"><i class="fa fa-chart-bar me-1"></i> Informe</a>
                                                        <a class="btn btn-xs btn-outline-info mb-0" title="Ver Envíos del Curso"
                                                            href="{{ route('informe.envios.curso', ['id_curso' => $curso->id]) }}"><i class="fa fa-code me-1"></i> Envíos</a>
                                                    @endcan
                                                    @can('crear problemas')
                                                        <a class="btn btn-xs btn-outline-success mb-0" title="Crear Problema en este Curso"
                                                            href="{{ route('problemas.crear', ['id_curso' => $curso->id]) }}"><i class="fa fa-plus me-1"></i> Problema</a>
                                                    @endcan
                                                    @can('crear certamen')
                                                        <a class="btn btn-xs btn-outline-warning mb-0" title="Crear Evaluación en este Curso"
                                                            href="{{ route('certamen.crear', ['id_curso' => $curso->id]) }}"><i class="fa fa-plus me-1"></i> Evaluación</a>
                                                    @endcan
                                                    @can('editar curso')
                                                        <a class="btn btn-xs btn-outline-info mb-0" title="Editar Curso"
                                                            href="{{ route('cursos.editar', ['id' => $curso->id]) }}"><i class="fa fa-pencil me-1"></i> Editar</a>
                                                    @endcan
                                                    @can('eliminar curso')
                                                        <form action="{{ route('cursos.eliminar', ['id' => $curso->id]) }}"
                                                            method="POST"
                                                            onsubmit="event.preventDefault();submitFormEliminar('{{ 'el curso ' . $curso->nombre }}', {{ $curso->id }})"
                                                            id="eliminarForm_{{ $curso->id }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-outline-danger mb-0" title="Eliminar Curso"><i class="fa fa-fw fa-trash me-1"></i> Eliminar</button>
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
@endpush
