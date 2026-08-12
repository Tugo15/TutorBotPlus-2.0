@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Categorías de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Categorías de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-tags me-2 text-primary"></i>Gestión de Categorías de Problemas</h6>
                            <p class="text-xs text-secondary mb-0">Administre las categorías temáticas empleadas para clasificar los ejercicios.</p>
                        </div>
                        @can('crear categoría de problema')
                        <a class="btn btn-sm btn-primary mb-0" href="{{ route('categorias.crear') }}"><i class="fa fa-plus me-1"></i> Crear Categoría</a>
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
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar categoría de problema', 'eliminar categoría de problema'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categorias as $categoria)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $categoria->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $categoria->fecha ? $categoria->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar categoría de problema', 'eliminar categoría de problema'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar categoría de problema')
                                                        <a class="btn btn-sm btn-outline-warning me-1" title="Editar Categoría"
                                                            href="{{ route('categorias.editar', ['id' => $categoria->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar categoría de problema')
                                                        <form action="{{ route('categorias.eliminar', ['id' => $categoria->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'la categoria '.$categoria->nombre}}', {{$categoria->id}})" id="eliminarForm_{{$categoria->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Categoría"><i
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
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush
