@extends('layout_plataforma.app', ['title_html' => 'Evaluaciones', 'title'=>'Evaluaciones', 'breadcrumbs'=>[["nombre"=>"Evaluaciones"]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="card border-danger">
            <div class="card-body px-5">
                <table id="table" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Curso</th>
                            <th>Cantidad Problemas</th>
                            <th>Penalización por error</th>
                            <th>¿Finalizado?</th>
                            <th>Tiempo de Desarrollo</th>
                            <th>Ejercicios Resueltos</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Termino</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluaciones as $evaluacion)
                            <tr>
                                <td><a href="{{route('certamenes.ver', ['id_certamen'=>$evaluacion->id])}}">{{$evaluacion->nombre}}</a></td>
                                <td>{{$evaluacion->curso->nombre}}</td>
                                <td>{{$evaluacion->cantidad_problemas}}</td>
                                <td>{{$evaluacion->penalizacion_error}}</td>
                                <td>{{$evaluacion->estado_finalizado? 'Si' : 'No'}}</td>
                                <td>{{isset($evaluacion->estado_finalizado)? gmdate('H:i:s', $evaluacion->tiempo_desarrollo) : "-"}}</td>
                                <td>{{isset($evaluacion->maximo_resuelto)? $evaluacion->maximo_resuelto.'/'.$evaluacion->cantidad_problemas : '-/'.$evaluacion->cantidad_problemas}}</td>
                                <td>{{$evaluacion->fecha_inicio}}</td>
                                <td>{{$evaluacion->fecha_termino}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    
@endpush
