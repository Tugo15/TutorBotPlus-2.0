<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-3">
                <div class="d-flex justify-content-between align-items-center pb-3">
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users me-2 text-primary"></i>Informe de Estudiantes</h6>
                        <p class="text-xs text-secondary mb-0">Rendimiento de los estudiantes en este problema.</p>
                    </div>
                    <a href="{{route('informes.problemas.index', ['id'=>$id_problema])}}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
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
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                    Intentos y Feedback
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                    Métricas de Evaluación
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                    Tiempo
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                    Estado
                                </th>
                                @canany(['ver informe del problema'])
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Acción</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($envios as $envio)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $envio->firstname }} {{ $envio->lastname }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $envio->rut }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-secondary me-1" title="Intentos Realizados">Int: {{ $envio->cant_intentos }}</span>
                                        <span class="badge badge-sm bg-gradient-info" title="Cantidad de Feedback">FB: {{ $envio->cant_retroalimentacion }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-xs text-secondary mb-0" title="Casos de Prueba">Casos: <span class="badge bg-gradient-dark text-xxs">{{ $envio->max_casos_resueltos }} / {{ $problema_estadistica->total_casos }}</span></p>
                                        <p class="text-xs text-secondary mb-0" title="Puntaje">Puntos: <span class="badge bg-gradient-dark text-xxs">{{ $envio->max_puntaje }} / {{ $problema_estadistica->puntaje_total }}</span></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="text-xs font-weight-bold">{{isset($envio->diferencia)? $envio->diferencia : 0}}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm @if ($envio->solucionado == true) bg-gradient-success @else bg-gradient-secondary @endif">{{ $envio->solucionado == true ? 'Solucionado' : 'Pendiente' }}</span>
                                    </td>
                                    @canany(['ver informe del problema'])
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('ver informe del problema')
                                                    <a class="btn btn-xs btn-outline-info mb-0" title="Ver Envíos del Estudiante" href="{{route('informe.envios.problema', ['id_problema'=>$envio->id_problema, 'id_curso'=>$envio->id_curso, 'id_usuario'=>$envio->id_usuario])}}"><i class="fa fa-code me-1"></i> Envíos</a>
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
    <script>
        new DataTable('#tabla_estudiantes', {
            responsive: true,
            order: [
                [0, 'ASC']
            ],
            dom: "<'row pb-3 px-4 pt-4 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row pt-3 px-4 pb-4 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>"
        });
    </script>
@endpush
