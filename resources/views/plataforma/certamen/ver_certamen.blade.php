@php
    $breadcrumbsCertamen = [['nombre' => 'Cursos', 'route' => route('cursos.listado')]];
    if ($certamen->curso) {
        $breadcrumbsCertamen[] = ['nombre' => $certamen->curso->nombre, 'route' => route('problemas.listado', ['id' => $certamen->curso->id])];
    }
    $breadcrumbsCertamen[] = ['nombre' => $certamen->nombre];
@endphp
@extends('layout_plataforma.app', ['title_html' => $certamen->nombre, 'title' => 'Evaluación - ' . $certamen->nombre, 'breadcrumbs' => $breadcrumbsCertamen, 'show_breadcrumbs' => true])

@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger shadow-sm h-100" style="min-height:40rem">
                    <div class="card-header">
                        Descripción
                    </div>
                    <div class="card-body p-4 text-wrap" id="body_markdown">
                        {!! Str::markdown($certamen->descripcion, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger" style="height:40rem">
                    <div class="card-body px-3">
                        @if (isset($res_certamen) && !$res_certamen->finalizado)
                            @if ($certamen->disponibilidad)
                                <div class="alert alert-success border-success text-white py-2 px-3 mb-3 text-center" role="alert">
                                    <i class="fa fa-unlock-alt me-1"></i>
                                    <small class="font-weight-bold">
                                        Tienes esta evaluación en curso. Presiona "Volver a la Evaluación" para continuar.
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-secondary text-white py-2 px-3 mb-3 text-center" role="alert">
                                    <i class="fa fa-clock-o me-1"></i>
                                    <small class="font-weight-bold">
                                        El plazo para esta evaluación ha finalizado.
                                    </small>
                                </div>
                            @endif
                        @endif
                        <div class="row px-5">
                            @if (isset($res_certamen))
                                <a class="btn btn-primary text-nowrap btn-block {{ ($res_certamen->finalizado == true || !$certamen->disponibilidad) ? 'disabled' : '' }}"
                                    href="{{ route('certamenes.resolucion', ['token' => $res_certamen->token]) }}"
                                    role="button">{{ $res_certamen->finalizado == true ? 'Has finalizado la evaluación' : 'Volver a la Evaluación' }}</a>
                            @else
                                @if (!empty($certamen->password))
                                    <button type="button" class="btn btn-primary text-nowrap btn-block {{ $certamen->disponibilidad ? '' : 'disabled' }}"
                                        @if($certamen->disponibilidad) data-bs-toggle="modal" data-bs-target="#modalPasswordCertamen" @else disabled @endif>
                                        <i class="fa fa-lock me-1"></i> Resolver Evaluación
                                    </button>
                                @else
                                    <a class="btn btn-primary text-nowrap btn-block {{ $certamen->disponibilidad ? '' : 'disabled' }}"
                                        href="{{ route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]) }}"
                                        role="button">{{ $certamen->disponibilidad ? 'Resolver Evaluación' : 'Evaluación No Disponible' }}</a>
                                @endif
                            @endif
                        </div>
                        @can('ver informe del certamen')
                            <div class="row px-5 mt-2 mb-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block" href="{{ route('informe.certamen', ['id_certamen' => $certamen->id]) }}"
                                    role="button">Ver Informe de la Evaluación</a>
                            </div>
                        @endcan
                        @can('editar certamen')
                            <div class="row px-5 mt-2 mb-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                    href="{{ route('certamen.editar', ['id' => $certamen->id]) }}" role="button">Editar Evaluación</a>
                            </div>
                        @endcan
                        <hr>
                        <h6 class="ms-3 mt-3"><strong>Información:</strong></h6>
                        <ul class="list-group mt-3">
                            <li class="list-group-item"><strong>Curso:</strong>
                                {{ $certamen->curso->nombre }}
                            </li>
                            @if (isset($certamen->fecha_inicio))
                                <li class="list-group-item"><strong>Fecha de Inicio:</strong> {{ $certamen->fecha_inicio }}
                                </li>
                            @endif
                            @if (isset($certamen->fecha_termino))
                                <li class="list-group-item"><strong>Fecha de Término:</strong>
                                    {{ $certamen->fecha_termino }}</li>
                            @endif
                            @if (isset($res_certamen) && $res_certamen->finalizado == true)
                                <li class="list-group-item"><strong>Finalizado:</strong>
                                    {{ $res_certamen->fecha_finalizado ? \Carbon\Carbon::parse($res_certamen->fecha_finalizado)->locale('es_ES')->isoFormat('lll') : $certamen->fecha_termino }}</li>
                            @elseif (isset($res_certamen) && $res_certamen->finalizado == false)
                                <li class="list-group-item text-success font-weight-bold">
                                    <i class="fa fa-spinner fa-spin me-1"></i> <strong>Estado:</strong> En Curso (Abierto)
                                </li>
                                @if(isset($res_certamen->fecha_termino_efectiva))
                                    <li class="list-group-item text-dark">
                                        <strong>Tu plazo límite:</strong> {{ $res_certamen->fecha_termino_efectiva->locale('es_ES')->isoFormat('lll') }}
                                    </li>
                                @endif
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Acceso:</strong>
                                @if(!empty($certamen->password))
                                    <span class="badge bg-warning text-dark"><i class="fa fa-lock me-1"></i>Requiere Contraseña</span>
                                @else
                                    <span class="badge bg-success"><i class="fa fa-unlock me-1"></i>Libre</span>
                                @endif
                            </li>
                            @if($certamen->restriccion_red)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Red:</strong>
                                    <span class="badge bg-danger"><i class="fa fa-university me-1"></i>Red Institucional Requerida</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            @if (isset($res_certamen) && $res_certamen->finalizado == true)
                <div class="col">
                    <div class="card border-danger mt-3" style="height:auto">
                        <div class="card-header">
                            Resultado
                        </div>
                        <div class="card-body px-3">
                            <div class="table-responsive">
                                <table id="table-res" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Problema</th>
                                            <th>Casos Resueltos</th>
                                            <th>Puntaje Obtenido</th>
                                            <th>¿Solucionado?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($resultado as $item)
                                            <tr>
                                                <td>{{ $item->nombre }}</td>
                                                <td>{{ $item->max_casos_resueltos . ' / ' . $item->total_casos }}</td>
                                                <td>{{ $item->maximo_puntaje . ' / ' . $item->puntos_total }}</td>
                                                <td>{{ $item->resuelto ? (!empty($item->variacion_formato) ? 'Sí - Aceptado (Variación de Formato)' : 'Sí') : 'No' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!isset($res_certamen) && !empty($certamen->password))
        <div class="modal fade" id="modalPasswordCertamen" tabindex="-1" aria-labelledby="modalPasswordCertamenLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('certamenes.iniciar_resolucion', ['id_certamen' => $certamen->id]) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-bold" id="modalPasswordCertamenLabel">
                                <i class="fa fa-key text-warning me-2"></i>Contraseña de Acceso Requerida
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-secondary mb-3">
                                Esta evaluación está protegida. Ingrese la clave de acceso proporcionada por su docente para comenzar a rendirla:
                            </p>
                            <div class="form-group mb-0">
                                <label for="input_password_certamen" class="form-control-label font-weight-bold text-sm">Contraseña de la Evaluación*</label>
                                <input type="password" name="password" id="input_password_certamen" class="form-control" placeholder="Ingrese la contraseña aquí..." required autofocus autocomplete="off">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-unlock me-1"></i> Validar y Comenzar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('js')
    <script>
        var table = document.querySelector("#body_markdown table")
        if (table != null) {
            var table_body = table.querySelector("tbody")
            table.classList.add("table")
            table.classList.add("table-bordered")
            table.classList.add("table-hover")
            table.classList.add("mt-3")
            table.style.width = "auto"
            table_body.classList.add("table-group-divider")
        }
    </script>
@endpush
