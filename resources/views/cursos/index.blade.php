@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Cursos'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Cursos'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Cursos</h6>
                        @can('crear curso')
                        <a class="btn btn-primary active" href="{{ route('cursos.crear') }}">Crear</a>
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
                                        Código
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar curso', 'eliminar curso', 'ver informe del curso'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cursos as $curso)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $curso->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $curso->codigo }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $curso->fecha ? $curso->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar curso', 'eliminar curso', 'ver informe del curso'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('ver informe del curso')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('informe.curso', ['id_curso' => $curso->id]) }}">Informe</a>
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('informe.envios.curso', ['id_curso' => $curso->id]) }}">Envios</a>
                                                    @endcan
                                                    @can('editar curso')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('cursos.editar', ['id' => $curso->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar curso')
                                                        <form action="{{ route('cursos.eliminar', ['id' => $curso->id]) }}"
                                                            method="POST"
                                                            onsubmit="event.preventDefault();submitFormEliminar('{{ 'el curso ' . $curso->nombre }}', {{ $curso->id }})"
                                                            id="eliminarForm_{{ $curso->id }}">
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
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script>
@endpush
