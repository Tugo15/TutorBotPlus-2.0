<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-0">
                <div class="d-flex justify-content-center align-items-center pb-2 position-relative w-100">
                    <div class="text-center">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users me-2 text-primary"></i>Informe de Estudiantes</h6>
                        <p class="text-xs text-secondary mb-0">Rendimiento de los estudiantes en este certamen. <span class="badge badge-sm bg-gradient-secondary ms-2 text-xxs">NR: No Resuelto</span></p>
                    </div>
                    <a href="{{ route('certamen.index') }}" class="btn btn-sm btn-outline-secondary mb-0 position-absolute end-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-0">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0" id="tabla_estudiantes">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Nombre Completo
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Rut
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Resueltos
                                </th>
                                @for ($i = 1; $i <= $certamen_estadistica->cantidad_problemas; $i++)
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        {{ 'P' . $i }}
                                    </th>
                                @endfor
                                @canany(['ver informe del problema'])
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Acción
                                    </th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listado_resultados as $item)
                                <tr>
                                    <td class="text-center align-middle">
                                        <div class="d-flex px-2 py-1 justify-content-center text-center">
                                            <div class="d-flex flex-column justify-content-center text-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $item->firstname }} {{ $item->lastname }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex px-2 py-1 justify-content-center text-center">
                                            <div class="d-flex flex-column justify-content-center text-center">
                                                <h6 class="mb-0 text-sm text-secondary">{{ $item->rut }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-info">{{ $item->problemas_resueltos }} / {{ $certamen_estadistica->cantidad_problemas }}</span>
                                    </td>
                                    @for ($i = 0; $i < $certamen_estadistica->cantidad_problemas; $i++)
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm {{ isset($item->resultados[$i]) ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                {{ isset($item->resultados[$i]) ? $item->resultados[$i]->maximo_puntaje . '/' . $item->resultados[$i]->puntos_total : 'NR' }}
                                            </span>
                                        </td>
                                    @endfor

                                    @canany(['ver informe del problema'])
                                        <td class="align-middle text-center">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('ver informe del problema')
                                                    <a class="btn btn-xs btn-outline-info mb-0" title="Ver Detalles del Certamen"
                                                        href="{{ route('informe.certamen.detalle', ['id_certamen' => $certamen_estadistica->id, 'id_res_certamen' => $item->id]) }}"><i class="fa fa-info-circle me-1"></i> Detalle</a>
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
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script>
        new DataTable('#tabla_estudiantes', {
            language: typeof espaniol !== 'undefined' ? espaniol : {},
            responsive: true,
            order: [
                [0, 'ASC']
            ],
            dom: "<'row pb-2 px-4 pt-2 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row pt-3 px-4 pb-4 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>"
        });
    </script>
@endpush
