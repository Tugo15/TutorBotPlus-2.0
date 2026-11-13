@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Envios del Certamen'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Envios realizado en la Evaluación "' . $certamen->nombre . '"',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
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

                    @if(isset($res_certamen))
                        <div class="mx-4 mt-3 p-3 bg-gray-100 rounded border d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="text-sm font-weight-bold text-dark"><i class="fa fa-user-circle text-primary me-1"></i> {{ $res_certamen->usuario->firstname ?? 'Estudiante' }} {{ $res_certamen->usuario->lastname ?? '' }}</span>
                                    <span class="badge bg-white text-secondary border font-monospace text-xxs">RUT: {{ $res_certamen->usuario->rut ?? 'N/A' }}</span>
                                    @if($res_certamen->finalizado)
                                        <span class="badge bg-secondary text-xxs"><i class="fa fa-lock me-1"></i> Finalizado ({{ isset($res_certamen->fecha_finalizado) ? \Carbon\Carbon::parse($res_certamen->fecha_finalizado)->format('d/m/Y H:i:s') : 'Sí' }})</span>
                                    @else
                                        <span class="badge bg-success text-xxs"><i class="fa fa-spinner fa-spin me-1"></i> En Curso (Abierto)</span>
                                    @endif
                                    @if(isset($res_certamen->fecha_termino_personalizada))
                                        <span class="badge bg-info text-white text-xxs" title="Plazo extendido"><i class="fa fa-clock-o me-1"></i> Hasta {{ \Carbon\Carbon::parse($res_certamen->fecha_termino_personalizada)->format('H:i:s d/m') }}</span>
                                    @endif
                                </div>
                                <small class="text-xs text-muted">
                                    Puntaje Total: <strong>{{ $res_certamen->puntaje_obtenido ?? 0 }} pts</strong> &bull; Problemas Resueltos: <strong>{{ $res_certamen->problemas_resueltos ?? 0 }}</strong>
                                </small>
                            </div>
                            <div>
                                @canany(['editar certamen', 'ver informe del certamen'])
                                    @if($res_certamen->finalizado)
                                        <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold mb-0" data-bs-toggle="modal" data-bs-target="#modalReabrirDetalle">
                                            <i class="fa fa-unlock me-1"></i> Reabrir Acceso a la Evaluación
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#modalReabrirDetalle">
                                            <i class="fa fa-clock-o me-1"></i> Gestionar Tiempo Adicional
                                        </button>
                                    @endif
                                @endcanany
                            </div>
                        </div>
                    @endif

                    <h6 class="ms-4 mt-3 font-weight-bold">Resumen de Resultados</h6>
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
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Estado</th>
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
                                            <td class="align-middle text-center text-sm">
                                                @if($item->resuelto)
                                                    <span class="badge badge-sm bg-gradient-success">{{ !empty($item->variacion_formato) ? 'Aceptado (Variación de Formato)' : 'Aceptado' }}</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">No Resuelto</span>
                                                @endif
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

    @if(isset($res_certamen))
        @canany(['editar certamen', 'ver informe del certamen'])
            <div class="modal fade" id="modalReabrirDetalle" tabindex="-1" aria-labelledby="modalLabelDetalle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border shadow-lg">
                        <form action="{{ route('certamen.reabrir_resolucion', ['id_res_certamen' => $res_certamen->id]) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-white border-bottom py-3">
                                <h6 class="modal-title font-weight-bold text-dark" id="modalLabelDetalle">
                                    <i class="fa fa-unlock text-warning me-2"></i> {{ $res_certamen->finalizado ? 'Reabrir Acceso a Evaluación' : 'Gestionar Tiempo de Evaluación' }}
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-4 text-start">
                                <div class="p-3 bg-gray-100 rounded border mb-3">
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Estudiante</span>
                                            <strong class="text-xs text-dark">{{ $res_certamen->usuario->firstname ?? 'Estudiante' }} {{ $res_certamen->usuario->lastname ?? '' }}</strong>
                                        </div>
                                        <div class="col-5">
                                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">RUT</span>
                                            <span class="text-xs text-dark font-monospace">{{ $res_certamen->usuario->rut ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-12 mt-2 pt-2 border-top">
                                            <span class="text-xxs text-uppercase text-muted d-block font-weight-bold">Estado Actual</span>
                                            @if($res_certamen->finalizado)
                                                <span class="badge bg-secondary text-xxs"><i class="fa fa-lock me-1"></i> Finalizado ({{ isset($res_certamen->fecha_finalizado) ? \Carbon\Carbon::parse($res_certamen->fecha_finalizado)->format('d/m/Y H:i:s') : 'Sí' }})</span>
                                            @else
                                                <span class="badge bg-success text-xxs"><i class="fa fa-spinner fa-spin me-1"></i> En Curso (Abierto)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info py-2 px-3 text-xs mb-3 border-info text-white">
                                    <i class="fa fa-info-circle me-1"></i>
                                    @if($res_certamen->finalizado)
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
                                    <i class="fa fa-unlock me-1"></i> {{ $res_certamen->finalizado ? 'Confirmar y Reabrir Acceso' : 'Aplicar Tiempo' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcanany
    @endif
@endsection
