@extends('layout_plataforma.app', ['title_html' => 'Envíos', 'title'=>'Mis Envíos', "breadcrumbs"=>[["nombre"=>"Envíos"]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-body px-5">
                <div class="table-responsive">
                    <table id="table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Problema</th>
                                <th>Lenguaje</th>
                                <th>Curso</th>
                                <th>Estado</th>
                                <th>Restricción</th>
                                <th>Casos Resueltos</th>
                                <th>Puntaje</th>
                                <th>Inicio</th>
                                <th>Término</th>
                                <th class="no-sort">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($envios as $envio)
                                <tr>
                                    <td>{{$envio->id_envio}}</td>
                                    <td><a href="{{route('problemas.ver', ['codigo'=>$envio->codigo_problema, 'id_curso'=>$envio->id_curso])}}">{{$envio->nombre_problema}}</a></td>
                                    <td>{{$envio->nombre_lenguaje}}</td>
                                    <td><a href="{{route('problemas.listado', ["id"=>$envio->id_curso])}}">{{$envio->nombre_curso}}</a></td>
                                    <td>
                                        @if($envio->solucionado == true)
                                            <span class="badge text-bg-success">{{ !empty($envio->variacion_formato) ? 'Aceptado (Variación de Formato)' : 'Accepted' }}</span>
                                        @elseif($envio->estado == "Error" || $envio->estado == "Rechazado")
                                            <span class="badge text-bg-danger">{{ $envio->resultado ?? $envio->estado }}</span>
                                        @elseif($envio->estado == "En Proceso")
                                            <span class="badge text-bg-warning">In Process</span>
                                        @else
                                            <span class="badge text-bg-secondary">Sin Evaluación</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($envio->restricciones) && trim($envio->restricciones) !== '')
                                            @if($envio->cumple_restricciones === 1 || $envio->cumple_restricciones === true)
                                                <span class="badge text-bg-success" title="{{ $envio->restricciones }}">Cumple</span>
                                            @elseif($envio->cumple_restricciones === 0 || $envio->cumple_restricciones === false)
                                                <span class="badge text-bg-danger" title="{{ $envio->restricciones }}">No cumple</span>
                                            @else
                                                <span class="badge text-bg-warning" title="{{ $envio->restricciones }}">Pendiente</span>
                                            @endif
                                        @else
                                            <span class="text-muted text-xs">-</span>
                                        @endif
                                    </td>
                                    <td>{{$envio->cant_casos_resuelto}} de {{$envio->total_casos}}</td>
                                    <td>{{$envio->puntaje}}</td>
                                    <td data-order="{{ $envio->inicio_ts ?? \App\Support\DateHelper::timestamp($envio->inicio ?? null) }}">{{$envio->inicio}}</td>
                                    <td data-order="{{ $envio->termino_ts ?? \App\Support\DateHelper::timestamp($envio->termino ?? null) }}">{{$envio->termino}}</td>
                                    <td><a href="{{route('envios.ver', ['token'=>$envio->token, 'from'=>'envios'])}}">Ver</a></td>
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
