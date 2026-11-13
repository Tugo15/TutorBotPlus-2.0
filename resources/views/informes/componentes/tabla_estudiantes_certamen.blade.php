<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 pb-3 w-100">
                    <div class="text-center text-md-start">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users me-2 text-primary"></i>Informe de Estudiantes</h6>
                        <p class="text-xs text-secondary mb-0">Rendimiento de los estudiantes en este certamen. <span class="badge badge-sm bg-gradient-secondary ms-2 text-xxs">NR: No Resuelto</span></p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                        <a href="{{ route('informe.certamen.exportar', ['id_certamen' => $certamen_estadistica->id]) }}" class="btn btn-sm btn-success mb-0">
                            <i class="fa fa-file-csv me-1"></i> Exportar CSV
                        </a>
                        <a href="{{ route('certamen.index', request('id_curso') ? ['id_curso' => request('id_curso')] : []) }}" class="btn btn-sm btn-outline-secondary mb-0">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
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
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                    Estado
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
                                    <td class="align-middle text-center text-sm">
                                        @if($item->finalizado)
                                            <span class="badge badge-sm bg-gradient-secondary"><i class="fa fa-lock me-1"></i> Finalizado</span>
                                            @if(isset($item->fecha_finalizado))
                                                <br><small class="text-xxs text-muted">{{ \Carbon\Carbon::parse($item->fecha_finalizado)->format('d/m H:i') }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-sm bg-gradient-success"><i class="fa fa-spinner fa-spin me-1"></i> En Curso</span>
                                        @endif
                                    </td>
                                    @for ($i = 0; $i < $certamen_estadistica->cantidad_problemas; $i++)
                                        @php
                                            $resProb = $item->resultados[$i] ?? null;
                                        @endphp
                                        <td class="align-middle text-center text-sm">
                                            @if($resProb && $resProb->entregado)
                                                @if($resProb->resuelto)
                                                    <span class="badge badge-sm bg-gradient-success" title="{{ $resProb->nombre_problema ?? 'Problema' }}: Resuelto correctamente ({{ $resProb->cantidad_intentos }} intento/s)">
                                                        {{ $resProb->maximo_puntaje }}/{{ $resProb->puntos_total }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-warning" title="{{ $resProb->nombre_problema ?? 'Problema' }}: Puntaje parcial ({{ $resProb->cantidad_intentos }} intento/s)">
                                                        {{ $resProb->maximo_puntaje }}/{{ $resProb->puntos_total }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary" title="{{ isset($resProb->nombre_problema) ? $resProb->nombre_problema . ': ' : '' }}Sin entregas">NR</span>
                                            @endif
                                        </td>
                                    @endfor

                                    @canany(['ver informe del problema', 'editar certamen', 'ver informe del certamen'])
                                        <td class="align-middle text-center">
                                            <div class="d-flex px-2 py-1 justify-content-center align-items-center gap-1">
                                                <a class="btn btn-xs btn-outline-info mb-0" title="Ver Detalles del Certamen"
                                                    href="{{ route('informe.certamen.detalle', ['id_certamen' => $certamen_estadistica->id, 'id_res_certamen' => $item->id]) }}"><i class="fa fa-info-circle me-1"></i> Detalle</a>
                                                @canany(['editar certamen', 'ver informe del certamen'])
                                                    @if($item->finalizado)
                                                        <button type="button" class="btn btn-xs btn-outline-warning mb-0" data-bs-toggle="modal" data-bs-target="#modalReabrir_{{ $item->id }}" title="Reabrir acceso a este estudiante">
                                                            <i class="fa fa-unlock me-1"></i> Reabrir
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-xs btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalReabrir_{{ $item->id }}" title="Gestionar tiempo adicional">
                                                            <i class="fa fa-clock-o me-1"></i> + Tiempo
                                                        </button>
                                                    @endif
                                                @endcanany
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

@canany(['editar certamen', 'ver informe del certamen'])
    @foreach ($listado_resultados as $item)
        <div class="modal fade" id="modalReabrir_{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabel_{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border shadow-lg">
                    <form action="{{ route('certamen.reabrir_resolucion', ['id_res_certamen' => $item->id]) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-white border-bottom py-3">
                            <h6 class="modal-title font-weight-bold text-dark" id="modalLabel_{{ $item->id }}">
                                <i class="fa fa-unlock text-warning me-2"></i> {{ $item->finalizado ? 'Reabrir Acceso a Evaluación' : 'Gestionar Tiempo de Evaluación' }}
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-4 text-start">
                            <div class="p-3 bg-gray-100 rounded border mb-3">
                                <div class="row g-2">
                                    <div class="col-7">
                                        <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Estudiante</span>
                                        <strong class="text-xs text-dark">{{ $item->firstname }} {{ $item->lastname }}</strong>
                                    </div>
                                    <div class="col-5">
                                        <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">RUT</span>
                                        <span class="text-xs text-dark font-monospace">{{ $item->rut }}</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Estado Actual</span>
                                        @if($item->finalizado)
                                            <span class="badge bg-secondary text-xxs"><i class="fa fa-lock me-1"></i> Finalizado ({{ isset($item->fecha_finalizado) ? \Carbon\Carbon::parse($item->fecha_finalizado)->format('d/m/Y H:i:s') : 'Sí' }})</span>
                                        @else
                                            <span class="badge bg-success text-xxs"><i class="fa fa-spinner fa-spin me-1"></i> En Curso (Abierto)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 px-3 text-xs mb-3 border-info text-white">
                                <i class="fa fa-info-circle me-1"></i>
                                @if($item->finalizado)
                                    Al reabrir el acceso, el estudiante podrá ingresar nuevamente a la evaluación desde su panel con sus ejercicios y código previo intactos.
                                @else
                                    Puedes otorgar tiempo adicional para que el estudiante cuente con minutos extra antes de que finalice su intento.
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bold text-dark">Tiempo Adicional a Otorgar:</label>
                                <select name="minutos_adicionales" class="form-select form-select-sm">
                                    <option value="0" selected>Sin tiempo extra (usar horario vigente del certamen)</option>
                                    <option value="5">+ 5 Minutos</option>
                                    <option value="10">+ 10 Minutos</option>
                                    <option value="15">+ 15 Minutos (Recomendado si ya cerró)</option>
                                    <option value="20">+ 20 Minutos</option>
                                    <option value="30">+ 30 Minutos</option>
                                    <option value="45">+ 45 Minutos</option>
                                    <option value="60">+ 1 Hora</option>
                                </select>
                                <small class="text-xxs text-muted d-block mt-1">
                                    * Si el horario general de la evaluación ya venció, se sumarán estos minutos a partir del momento actual.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top py-2">
                            <button type="button" class="btn btn-sm btn-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-warning text-dark font-weight-bold mb-0">
                                <i class="fa fa-unlock me-1"></i> {{ $item->finalizado ? 'Confirmar y Reabrir Acceso' : 'Aplicar Tiempo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endcanany
@push('js')
    @once
        <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
        <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    @endonce
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var stateExtraEst = typeof window.tutorBotStateCallbacks === 'function'
                ? window.tutorBotStateCallbacks('tabla_estudiantes')
                : { stateDuration: 60 * 60 * 24 * 30 };
            var tablaEst = new DataTable('#tabla_estudiantes', Object.assign({
                language: typeof espaniol !== 'undefined' ? espaniol : {},
                responsive: true,
                stateSave: true,
                order: [
                    [0, 'ASC']
                ],
                dom: typeof responsiveDomLayout !== 'undefined' ? responsiveDomLayout : "<'row mx-0 px-2 px-md-4 pb-2 pt-1 align-items-center gy-2'<'col-12 col-md-6 d-flex align-items-center flex-wrap'l><'col-12 col-md-6 d-flex align-items-center justify-content-md-end justify-content-start'f>>" +
                     "<'row mx-0'<'col-12 px-0 table-responsive'tr>>" +
                     "<'row mx-0 px-2 px-md-4 pt-3 pb-4 align-items-center gy-2'<'col-12 col-md-5 text-center text-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>"
            }, stateExtraEst));

            // Al cambiar de página, evitar saltar al fondo y subir suavemente al encabezado de la tabla
            tablaEst.on('page.dt', function () {
                var el = document.getElementById('tabla_estudiantes');
                if (el) {
                    var card = el.closest('.card') || el;
                    var offset = card.getBoundingClientRect().top + window.pageYOffset - 80;
                    window.scrollTo({ top: offset, behavior: 'smooth' });
                }
            });
        });
    </script>
@endpush
