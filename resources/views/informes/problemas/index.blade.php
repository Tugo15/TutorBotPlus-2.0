@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Informe de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Informe del Problema "' . $problema->nombre . '"',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-chart-bar me-2 text-primary"></i>Cursos Asociados al Problema</h6>
                            <p class="text-xs text-secondary mb-0">Seleccione un curso para ver el rendimiento y envíos de los estudiantes.</p>
                        </div>
                        <a href="{{route('problemas.index')}}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver al Problema</a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span class="text-sm"><i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span class="text-sm"><i class="fa fa-check-circle me-1"></i> {{ session('success') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Curso
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                        Métricas de Rendimiento
                                    </th>
                                    @canany(['ver informe del problema'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cursos as $curso)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $curso->nombre }}</h6>
                                                    <p class="text-xs text-secondary mb-0">Código: <span class="badge bg-gradient-primary text-xxs">{{ $curso->codigo }}</span></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-secondary me-2" title="Intentos Totales">Intentos: {{ $curso->cantidad_intentos }}</span>
                                            <span class="badge badge-sm bg-gradient-success" title="Ejercicios Resueltos">Resueltos: {{ $curso->cantidad_resueltos }}</span>
                                        </td>
                                        @canany(['ver informe del problema'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                                    @can('ver informe del problema')
                                                        <a class="btn btn-xs btn-outline-info mb-0" title="Ver Envíos del Curso"
                                                            href="{{ route('informe.envios.problema', ['id_curso' => $curso->id_curso, 'id_problema' => $problema->id]) }}"><i class="fa fa-code me-1"></i> Envíos</a>
                                                        <a class="btn btn-xs btn-outline-warning mb-0" title="Ver Informe Detallado"
                                                            href="{{ route('informe.problema', ['id_curso' => $curso->id_curso, 'id_problema' => $problema->id]) }}"><i class="fa fa-chart-pie me-1"></i> Informe</a>
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
