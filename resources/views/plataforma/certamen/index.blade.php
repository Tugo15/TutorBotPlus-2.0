@extends('layout_plataforma.app', ['title_html' => 'Evaluaciones', 'title'=>'Evaluaciones', 'breadcrumbs'=>[["nombre"=>"Evaluaciones"]]])
@section('content')
    <div class="container-fluid px-3 px-md-4">
        @include('components.alert')
        <div class="card border-danger">
            <div class="card-body px-3 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0"><strong>Evaluaciones Disponibles</strong></h5>
                        <span class="badge text-bg-light border fw-semibold">{{ count($evaluaciones) }}</span>
                    </div>
                    <a href="{{ route('cursos.listado') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center mb-0">
                        <i class="fa fa-arrow-left me-1"></i> Mis Cursos
                    </a>
                </div>
                <hr class="mt-2 mb-4">
                <div class="table-responsive">
                    <table id="table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Curso</th>
                                <th class="text-center">Problemas</th>
                                <th class="text-center">Penalización</th>
                                <th class="text-center">¿Finalizado?</th>
                                <th class="text-center">Tiempo Desarrollo</th>
                                <th class="text-center">Resueltos</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Término</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluaciones as $evaluacion)
                                <tr>
                                    <td><a class="fw-semibold" href="{{route('certamenes.ver', ['id_certamen'=>$evaluacion->id])}}">{{$evaluacion->nombre}}</a></td>
                                    <td>{{$evaluacion->curso->nombre}}</td>
                                    <td class="text-center">{{$evaluacion->cantidad_problemas}}</td>
                                    <td class="text-center">{{$evaluacion->penalizacion_error}}</td>
                                    <td class="text-center">
                                        @if($evaluacion->estado_finalizado)
                                            <span class="badge text-bg-success">Sí</span>
                                        @else
                                            <span class="badge text-bg-light border">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{isset($evaluacion->estado_finalizado) && isset($evaluacion->tiempo_desarrollo) ? gmdate('H:i:s', $evaluacion->tiempo_desarrollo) : "-"}}</td>
                                    <td class="text-center">{{isset($evaluacion->maximo_resuelto)? $evaluacion->maximo_resuelto.'/'.$evaluacion->cantidad_problemas : '-/'.$evaluacion->cantidad_problemas}}</td>
                                    <td data-order="{{ \App\Support\DateHelper::timestamp(method_exists($evaluacion, 'getRawOriginal') ? $evaluacion->getRawOriginal('fecha_inicio') : ($evaluacion->fecha_inicio ?? null)) }}">{{$evaluacion->fecha_inicio}}</td>
                                    <td data-order="{{ \App\Support\DateHelper::timestamp(method_exists($evaluacion, 'getRawOriginal') ? $evaluacion->getRawOriginal('fecha_termino') : ($evaluacion->fecha_termino ?? null)) }}">{{$evaluacion->fecha_termino}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}?v={{ @filemtime(public_path('assets/js/DataTables/gestion_initialize_es_cl.js')) ?: '1.0' }}"></script>
    
@endpush
