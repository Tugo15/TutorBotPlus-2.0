@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>"Gestión de Roles"])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Roles'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Roles</h6>
                        @can('crear rol')
                        <a class="btn btn-primary active" href="{{ route('roles.crear') }}">Crear</a>
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
                                        Permisos
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar rol', 'eliminar rol'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $rol)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $rol->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0 text-wrap">
                                                {{ implode(', ', $rol->permissions->pluck('name')->toArray()) }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $rol->fecha ? $rol->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar rol', 'eliminar rol'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar rol')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('roles.editar', ['id' => $rol->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar rol')
                                                        <form action="{{ route('roles.eliminar', ['id' => $rol->id]) }}"
                                                            method="POST" 
                                                            onsubmit="event.preventDefault();submitFormEliminar('{{'el rol '.$rol->nombre}}', {{$rol->id}})" id="eliminarForm_{{$rol->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger"><i
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