<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between">
                    <h6>Informe de Estudiantes</h6>
                </div>
            </div>

            <div class="card-body pb-0">
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
                                    Intentos Totales
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Soluciones Enviadas
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Ayuda Solicitada
                                </th>
                                @canany(['ver informe del problema'])
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Acción</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listado_estudiantes as $item)
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
                                                <h6 class="mb-0 text-sm">{{ $item->cantidad_intentos }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->cantidad_resueltos }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->cantidad_ra }} </td>
                                    @canany(['ver informe del problema'])
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('ver informe del problema')
                                                    <a class="btn btn-outline-warning" href="{{route('informe.envios.curso', ['id_curso'=>$curso_estadistica->id, 'id_usuario'=>$item->id_usuario])}}">Envios</a>
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
            <a href="{{route('cursos.index')}}" class="btn btn-outline-primary mt-3 mx-5">Volver</a>
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
