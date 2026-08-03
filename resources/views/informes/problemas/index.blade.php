@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Informe de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Informe del Problema "' . $problema->nombre . '"',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Cursos asociados al problema</h6>
                        <a href="{{route('problemas.index')}}" class="btn btn-outline-primary">Volver</a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cantidad de Intentos
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cantidad de Resueltos
                                    </th>
                                    @canany(['ver informe del problema'])
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
                                            <h6 class="mb-0 text-sm">{{ $curso->nombre }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $curso->codigo }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $curso->cantidad_intentos }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $curso->cantidad_resueltos }}</h6>
                                        </td>
                                        @canany(['ver informe del problema'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('ver informe del problema')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('informe.envios.problema', ['id_curso' => $curso->id_curso, 'id_problema' => $problema->id]) }}">Envios</a>
                                                            <a class="btn btn-outline-warning"
                                                            href="{{ route('informe.problema', ['id_curso' => $curso->id_curso, 'id_problema' => $problema->id]) }}">Informe</a>
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
@endpush
