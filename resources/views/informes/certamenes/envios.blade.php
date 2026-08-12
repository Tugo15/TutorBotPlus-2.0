@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Envios del Certamen'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Envios realizado en la Evaluación "' . $certamen->nombre . '"',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-file-alt me-2 text-primary"></i>Resultados y Envíos</h6>
                            <p class="text-xs text-secondary mb-0">Revisa los resultados obtenidos y envíos de esta evaluación.</p>
                        </div>
                        <a href="{{route('informe.certamen', ['id_certamen'=>$certamen->id])}}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
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
                    <h6 class="ms-4 mt-2 font-weight-bold">Resumen de Resultados</h6>
                    <div class="px-4 mb-4">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0 border rounded">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Problema</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Casos de Prueba</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Puntaje Obtenido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resultado as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ $item->nombre }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-info">{{ $item->max_casos_resueltos . ' / ' . $item->total_casos }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-success">{{ $item->maximo_puntaje . ' / ' . $item->puntos_total }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr class="horizontal dark my-3">
                    <h6 class="ms-4 font-weight-bold">Listado de Envíos</h6>
                    <div class="px-0">
                        @include('informes.componentes.tabla_envios')
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
