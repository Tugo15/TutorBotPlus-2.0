@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Gestión de Usuarios'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Usuarios'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users me-2 text-primary"></i>Gestión de Usuarios</h6>
                            <p class="text-xs text-secondary mb-0">Administre los usuarios registrados, asigne roles, edite información y gestione accesos.</p>
                        </div>
                        @can('crear usuario')
                        <div>
                            <a class="btn btn-sm btn-primary mb-0 me-2" href="{{ route('usuarios.crear') }}"><i class="fa fa-user-plus me-1"></i> Crear Usuario</a>
                            <a class="btn btn-sm btn-outline-primary mb-0" href="{{ route('usuarios.bulk') }}"><i class="fa fa-file-upload me-1"></i> Inserción Masiva</a>
                        </div>
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
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Usuario
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Roles
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cursos
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Email
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar usuario', 'eliminar usuario'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-inline-flex align-items-center py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $user->firstname ? $user->firstname . ' ' . $user->lastname : $user->username }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $user->username }} | {{ $user->rut }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($user->getRoleNames() as $rol)
                                                    <span class="badge bg-gradient-info text-xs">{{ $rol }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($user->cursos()->get() as $curso)
                                                    <span class="badge bg-gradient-primary text-xs" title="{{ $curso->nombre }}">{{ $curso->codigo }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->email }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $user->fecha ? $user->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar usuario', 'eliminar usuario'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar usuario')
                                                        <a class="btn btn-sm btn-outline-warning me-1" title="Editar Usuario"
                                                            href="{{ route('usuarios.editar', ['id' => $user->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar usuario')
                                                        @if (auth()->user()->id != $user->id)
                                                            <form action="{{ route('usuarios.eliminar', ['id' => $user->id]) }}"
                                                                method="POST" onsubmit="event.preventDefault();submitFormEliminar('el usuario {{ $user->username }}', {{ $user->id }})" id="eliminarForm_{{ $user->id }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Usuario"><i
                                                                        class="fa fa-fw fa-trash"></i></button>
                                                            </form>
                                                        @endif
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
<link href="{{asset('assets/js/DataTables/datatables.min.css')}}" rel="stylesheet">
 
<script src="{{asset('assets/js/DataTables/datatables.min.js')}}"></script>

<script src="{{asset('assets/js/DataTables/gestion_initialize_es_cl.js')}}"></script>
<script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush