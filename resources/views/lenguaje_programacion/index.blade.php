@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Gestión de Lenguajes de Programación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Lenguajes de Programación'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Lenguajes de Programación</h6>
                        @can('crear lenguaje de programación')
                        <a class="btn btn-primary active" href="{{ route('lenguaje_programacion.crear') }}">Crear</a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Código
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Abreviatura
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Extensión
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar lenguaje de programación', 'eliminar lenguaje de programación'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lenguajes as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->codigo }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>    
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->abreviatura }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->extension }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $item->fecha ? $item->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar lenguaje de programación', 'eliminar lenguaje de programación'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar lenguaje de programación')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('lenguaje_programacion.editar', ['id' => $item->id]) }}"><i class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar lenguaje de programación')
                                                        <form action="{{ route('lenguaje_programacion.eliminar', ['id' => $item->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el lenguaje '.$item->nombre}}', {{$item->id}})" id="eliminarForm_{{$item->id}}">
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