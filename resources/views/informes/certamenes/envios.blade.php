@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Envios del Certamen'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Envios realizado en la Evaluación "' . $certamen->nombre . '"',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Resultados y Envios</h6>
                        <a href="{{route('informe.certamen', ['id_certamen'=>$certamen->id])}}" class="btn btn-outline-primary">Volver</a>
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
                    <h6 class="ms-4">Resultados</h6>
                    <div class="px-4 my-3">
                        <ul class="list-group">

                        </ul>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0 my-3">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Problema</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Casos de Prueba</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Puntaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resultado as $item)
                                        <tr>

                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->max_casos_resueltos . ' / ' . $item->total_casos }}</td>
                                            <td>{{ $item->maximo_puntaje . ' / ' . $item->puntos_total }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <h6 class="my-3">Envios</h6>
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
