@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Gestión de Lenguajes de Programación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Lenguajes de Programación'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-warning"></i>Gestión de Lenguajes de Programación</h6>
                            <p class="text-xs text-secondary mb-0">Administre los lenguajes soportados por el Juez Virtual (Judge0).</p>
                        </div>
                        @can('crear lenguaje de programación')
                        <a class="btn btn-sm btn-dark mb-0" href="{{ route('lenguaje_programacion.crear') }}"><i class="fa fa-plus me-1"></i> Crear Lenguaje</a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Código Juez</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Nombre</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Abreviatura</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bold opacity-7 ps-2">Extensión</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Creado</th>
                                    @canany(['editar lenguaje de programación', 'eliminar lenguaje de programación'])
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bold opacity-7">Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lenguajes as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="badge border border-dark text-dark text-xxs px-2.5 py-1 bg-white">{{ $item->codigo }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>    
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $item->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="badge border border-warning text-dark text-xxs px-2 py-1 bg-white">{{ $item->abreviatura }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <span class="badge border border-info text-info text-xxs px-2 py-1 bg-white">{{ $item->extension }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-xs text-secondary font-weight-bold mb-0">
                                                {{ $item->fecha ? $item->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar lenguaje de programación', 'eliminar lenguaje de programación'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                                    @can('editar lenguaje de programación')
                                                        <a class="btn btn-xs btn-outline-info mb-0" title="Editar Lenguaje"
                                                            href="{{ route('lenguaje_programacion.editar', ['id' => $item->id]) }}"><i class="fa fa-pencil me-1"></i> Editar</a>
                                                    @endcan
                                                    @can('eliminar lenguaje de programación')
                                                        <form action="{{ route('lenguaje_programacion.eliminar', ['id' => $item->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el lenguaje '.$item->nombre}}', {{$item->id}})" id="eliminarForm_{{$item->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-outline-danger mb-0" title="Eliminar Lenguaje"><i class="fa fa-fw fa-trash me-1"></i> Eliminar</button>
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