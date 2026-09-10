@php
    $envios_list = isset($todos_envios) ? $todos_envios : (isset($envios) ? $envios : []);
    $problemas_unicos = collect($envios_list)->pluck('nombre_problema')->filter()->unique();
@endphp

<div class="row mt-4 mx-3">
    <div class="col-12">
        <div class="card shadow-xs border mb-4">
            <div class="card-header pb-0 border-bottom mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 gap-2">
                    <div>
                        <h6 class="mb-0 font-weight-bold text-dark">
                            <i class="fa fa-list-alt me-2 text-primary"></i>Registro General de Envíos e Intentos
                        </h6>
                        <p class="text-xs text-secondary mb-0">Listado detallado de envíos, códigos de solución e IP de origen.</p>
                    </div>
                </div>

                <!-- Filtros de Búsqueda Avanzados -->
                <div class="row g-2 pb-3 pt-2 bg-light rounded px-2 align-items-end border">
                    <div class="col-md-3 col-sm-6">
                        <label for="filtroEstadoEnvio" class="form-label text-xs font-weight-bold text-dark mb-1">
                            <i class="fa fa-filter text-primary me-1"></i>Estado del Envío:
                        </label>
                        <select id="filtroEstadoEnvio" class="form-select form-select-sm">
                            <option value="">Todos los Estados</option>
                            <option value="Aceptado">Aceptado / Accepted</option>
                            <option value="Rechazado">Rechazado / Wrong Answer</option>
                            <option value="Error">Error</option>
                            <option value="En Proceso">En Proceso</option>
                        </select>
                    </div>

                    @if(count($problemas_unicos) > 1)
                    <div class="col-md-4 col-sm-6">
                        <label for="filtroProblemaEnvio" class="form-label text-xs font-weight-bold text-dark mb-1">
                            <i class="fa fa-puzzle-piece text-info me-1"></i>Filtrar por Problema:
                        </label>
                        <select id="filtroProblemaEnvio" class="form-select form-select-sm">
                            <option value="">Todos los Problemas</option>
                            @foreach($problemas_unicos as $probNombre)
                                <option value="{{ $probNombre }}">{{ $probNombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3 col-sm-6">
                        <label for="filtroEstudianteEnvio" class="form-label text-xs font-weight-bold text-dark mb-1">
                            <i class="fa fa-user text-success me-1"></i>Estudiante / RUT:
                        </label>
                        <input type="text" id="filtroEstudianteEnvio" class="form-control form-control-sm" placeholder="Buscar Estudiante o RUT...">
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <button type="button" id="btnLimpiarFiltrosEnvio" class="btn btn-xs btn-outline-secondary w-100 mb-0">
                            <i class="fa fa-sync-alt me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0" id="tabla_envios_list">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estudiante</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Problema</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Lenguaje</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">IP Origen</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Estado</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Métricas</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Fecha</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($envios_list as $envio)
                                @php
                                    $esAceptado = (isset($envio->solucionado) && $envio->solucionado == true);
                                    $esError = (isset($envio->estado) && ($envio->estado == "Error" || $envio->estado == "Rechazado"));
                                    $nombreEstudiante = trim(($envio->firstname ?? '') . ' ' . ($envio->lastname ?? ''));
                                    $nombreProb = $envio->nombre_problema ?? $envio->nombre ?? 'N/A';
                                    $ipOrigen = $envio->ip_origen ?? '127.0.0.1';
                                    $codigoFuente = $envio->fuente_codigo ?? $envio->codigo ?? '// No se registró código en este envío';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $nombreEstudiante }}</h6>
                                                <p class="text-xs text-secondary mb-0">RUT: {{ $envio->rut ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $nombreProb }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge border border-dark text-dark bg-white text-xxs">{{ $envio->nombre_lenguaje ?? 'Lenguaje' }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge border border-dark text-dark bg-white font-monospace text-xxs">
                                            <i class="fa fa-network-wired me-1 text-primary"></i>{{ $ipOrigen }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($esAceptado)
                                            <span class="badge bg-success text-xxs"><i class="fa fa-check-circle me-1"></i> Aceptado</span>
                                        @elseif($esError)
                                            <span class="badge bg-danger text-xxs"><i class="fa fa-times-circle me-1"></i> {{ $envio->resultado ?? $envio->estado ?? 'Rechazado' }}</span>
                                        @else
                                            <span class="badge bg-warning text-xxs"><i class="fa fa-clock me-1"></i> En Proceso</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-xs mb-0">Casos: <span class="badge bg-gradient-info text-xxs">{{ $envio->cant_casos_resuelto ?? 0 }} / {{ $envio->total_casos ?? 0 }}</span></p>
                                        <p class="text-xs mb-0">Puntos: <span class="badge bg-gradient-dark text-xxs">{{ $envio->puntaje ?? 0 }}</span></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="text-xs font-weight-bold text-dark">
                                            {{ isset($envio->created_at) ? \Carbon\Carbon::parse($envio->created_at)->format('d/m/Y H:i') : '-' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-end">
                                        <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-1">
                                            <button type="button" class="btn btn-xs btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalCodigoEnvio_{{ $envio->id ?? $loop->index }}">
                                                <i class="fa fa-code me-1"></i> Ver Código
                                            </button>
                                            @if(isset($envio->token))
                                                <a class="btn btn-xs btn-outline-info mb-0" title="Ver Detalles del Envío" href="{{ route('envios.ver', ['token' => $envio->token]) }}">
                                                    <i class="fa fa-info-circle me-1"></i> Detalles
                                                </a>
                                            @endif
                                        </div>

                                        <!-- Modal de Código Fuente -->
                                        <div class="modal fade text-start" id="modalCodigoEnvio_{{ $envio->id ?? $loop->index }}" tabindex="-1" aria-labelledby="labelModalCodigo_{{ $envio->id ?? $loop->index }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content border shadow-lg">
                                                    <div class="modal-header bg-white border-bottom">
                                                        <div>
                                                            <h6 class="modal-title font-weight-bold text-dark" id="labelModalCodigo_{{ $envio->id ?? $loop->index }}">
                                                                <i class="fa fa-code text-primary me-2"></i>Código de {{ $nombreEstudiante }}
                                                            </h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                Problema: <strong>{{ $nombreProb }}</strong> | 
                                                                Lenguaje: <span class="badge bg-secondary text-xxs">{{ $envio->nombre_lenguaje ?? 'N/A' }}</span> | 
                                                                IP Origen: <span class="badge border border-dark text-dark bg-white font-monospace text-xxs"><i class="fa fa-network-wired me-1"></i>{{ $ipOrigen }}</span>
                                                            </p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3 bg-light">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-xs font-weight-bold text-dark">Código Fuente Enviado:</span>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="navigator.clipboard.writeText(document.getElementById('code_text_{{ $envio->id ?? $loop->index }}').innerText)">
                                                                <i class="fa fa-copy me-1"></i> Copiar Código
                                                            </button>
                                                        </div>
                                                        <pre class="bg-dark text-white p-3 rounded text-xs mb-0" style="max-height: 400px; overflow-y: auto;"><code id="code_text_{{ $envio->id ?? $loop->index }}">{{ $codigoFuente }}</code></pre>
                                                    </div>
                                                    <div class="modal-footer bg-white border-top py-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary mb-0" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
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
        document.addEventListener("DOMContentLoaded", function() {
            if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla_envios_list')) {
                var tableEnvios = $('#tabla_envios_list').DataTable({
                    responsive: true,
                    order: [[6, 'desc']],
                    dom: "<'row pb-3 px-4 pt-4 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row pt-3 px-4 pb-4 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>"
                });

                $('#filtroEstadoEnvio').on('change', function() {
                    tableEnvios.column(4).search(this.value).draw();
                });

                var filtroProb = $('#filtroProblemaEnvio');
                if (filtroProb.length) {
                    filtroProb.on('change', function() {
                        tableEnvios.column(1).search(this.value).draw();
                    });
                }

                $('#filtroEstudianteEnvio').on('keyup change', function() {
                    tableEnvios.column(0).search(this.value).draw();
                });

                $('#btnLimpiarFiltrosEnvio').on('click', function() {
                    $('#filtroEstadoEnvio').val('');
                    if (filtroProb.length) filtroProb.val('');
                    $('#filtroEstudianteEnvio').val('');
                    tableEnvios.search('').columns().search('').draw();
                });
            }
        });
    </script>
@endpush