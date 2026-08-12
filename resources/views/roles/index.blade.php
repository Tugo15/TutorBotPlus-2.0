@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>"Gestión de Roles"])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Roles'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-shield-alt me-2 text-primary"></i>Gestión de Roles y Permisos</h6>
                            <p class="text-xs text-secondary mb-0">Administre los roles del sistema y controle el nivel de acceso y permisos asignados.</p>
                        </div>
                        @can('crear rol')
                        <a class="btn btn-sm btn-primary mb-0" href="{{ route('roles.crear') }}"><i class="fa fa-plus me-1"></i> Crear Rol</a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Permisos
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar rol', 'eliminar rol'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $rol)
                                    <tr>
                                        <td>
                                            <div class="d-inline-flex align-items-center py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $rol->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 py-1">
                                                @foreach ($rol->permissions as $perm)
                                                    <span class="badge bg-gradient-info text-xs">{{ $perm->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $rol->fecha ? $rol->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar rol', 'eliminar rol'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar rol')
                                                        <a class="btn btn-sm btn-outline-warning me-1" title="Editar Rol"
                                                            href="{{ route('roles.editar', ['id' => $rol->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar rol')
                                                        <form action="{{ route('roles.eliminar', ['id' => $rol->id]) }}"
                                                            method="POST" 
                                                            onsubmit="event.preventDefault();submitFormEliminar('el rol {{ $rol->name }}', {{ $rol->id }})" id="eliminarForm_{{ $rol->id }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Rol"><i
                                                                    class="fa fa-fw fa-trash"></i></button>
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
<link href="{{asset('assets/js/DataTables/datatables.min.css')}}" rel="stylesheet">
 
<script src="{{asset('assets/js/DataTables/datatables.min.js')}}"></script>

<script src="{{asset('assets/js/DataTables/gestion_initialize_es_cl.js')}}"></script>

<script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush