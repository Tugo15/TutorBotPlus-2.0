@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Gestión de Usuarios'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Usuarios'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Usuarios</h6>
                        @can('crear usuario')
                        <div>
                            <a class="btn btn-primary active" href="{{ route('usuarios.crear') }}">Crear</a>
                            <a class="btn btn-primary active" href="{{ route('usuarios.bulk') }}">Inserción Masiva</a>
                        </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
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
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->username }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                @foreach ($user->getRoleNames() as $rol)
                                                    <div class="col-auto">
                                                        <span class="badge bg-gradient-secondary">{{ $rol }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                @foreach ($user->cursos()->get() as $curso)
                                                    <div class="col-auto">
                                                        <span
                                                            class="badge bg-gradient-secondary">{{ $curso->codigo }}</span>

                                                    </div>
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
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('usuarios.editar', ['id' => $user->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar usuario')
                                                        @if (auth()->user()->id != $user->id)
                                                            <form action="{{ route('usuarios.eliminar', ['id' => $user->id]) }}"
                                                                method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el usuario '.$user->nombre}}', {{$user->id}})" id="eliminarForm_{{$user->id}}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-danger"><i
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