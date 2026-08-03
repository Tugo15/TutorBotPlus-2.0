@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Evaluaciones'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Evaluaciones'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Evaluaciones</h6>
                        @can('crear certamen')
                        <a class="btn btn-primary active" href="{{ route('certamen.crear') }}">Crear</a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Nombre
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Curso
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Inicio
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Termino
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar certamen', 'eliminar certamen', 'ver informe del certamen'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($certamenes as $certamen)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $certamen->nombre }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $certamen->curso->nombre }}</h6>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $certamen->fecha_inicio ? $certamen->fecha_inicio : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $certamen->fecha_termino ? $certamen->fecha_termino : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $certamen->created_at ? $certamen->creado : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar problemas', 'eliminar problemas'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('ver informe del certamen')
                                                    <a class="btn btn-outline-warning"
                                                            href="{{ route('informe.certamen', ['id_certamen' => $certamen->id]) }}">Informe</a>
                                                    @endcan
                                                    @can('editar certamen')
                                                    <a class="btn btn-outline-warning"
                                                            href="{{ route('certamen.banco_problemas', ['id_certamen' => $certamen->id]) }}">Banco</a>
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('certamen.editar', ['id' => $certamen->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar certamen')
                                                        <form action="{{ route('certamen.eliminar', ['id' => $certamen->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el certamen '.$certamen->nombre}}', {{$certamen->id}})" id="eliminarForm_{{$certamen->id}}">
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
