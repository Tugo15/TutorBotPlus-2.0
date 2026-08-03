<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between">
                    <h6>Informe de Estudiantes</h6>
                </div>
            </div>

            <div class="card-body pb-0">
            <i class="ms-3" style="font-size: 15px;">NR: No Resuelto</i>
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0" id="tabla_estudiantes">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
                                    Completo
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Rut
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Resueltos
                                </th>
                                @for ($i = 1; $i <= $certamen_estadistica->cantidad_problemas; $i++)
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        {{ 'P' . $i }}
                                    </th>
                                @endfor
                                @canany(['ver informe del problema'])
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Acción</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listado_resultados as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->firstname }} {{ $item->lastname }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->rut }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">
                                                    {{ $item->problemas_resueltos . '/' . $certamen_estadistica->cantidad_problemas }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    @for ($i = 0; $i < $certamen_estadistica->cantidad_problemas; $i++)
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ isset($item->resultados[$i]) ? $item->resultados[$i]->maximo_puntaje . '/' . $item->resultados[$i]->puntos_total : 'NR' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                    @endfor

                                    @canany(['ver informe del problema'])
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('ver informe del problema')
                                                    <a class="btn btn-outline-warning"
                                                        href="{{ route('informe.certamen.detalle', ['id_certamen' => $certamen_estadistica->id, 'id_res_certamen' => $item->id]) }}">Detalle</a>
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
            <a href="{{ route('certamen.index') }}" class="btn btn-outline-primary mt-3 mx-5">Volver</a>
        </div>
    </div>
</div>
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script>
        new DataTable('#tabla_estudiantes', {
            responsive: true,
            order: [
                [0, 'ASC']
            ]
        });
    </script>
@endpush
