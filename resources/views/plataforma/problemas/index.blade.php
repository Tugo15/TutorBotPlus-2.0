@extends('layout_plataforma.app', ['title_html' => 'Problemas', 'title'=>$curso->nombre.' - Problemas', 'breadcrumbs'=>[["nombre"=>"Cursos", "route"=>route("cursos.listado")],["nombre"=>"Problemas"]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-body px-5">
                <table id="table" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Puntos</th>
                            <th>Categoria</th>
                            <th>¿Resuelto?</th>
                            <th>Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($problemas as $problema)
                            <tr>
                                <td><a href="{{route('problemas.ver', ['codigo'=>$problema->codigo, 'id_curso'=>$curso->id])}}">{{$problema->nombre}}</a></td>
                                <td>{{$problema->puntaje_total}}</td>
                                <td>{{$problema->categorias}}</td>
                                <td>{{$problema->resuelto? 'Si':'No'}}</td>
                                <td>{{$problema->creado}}</td>
                            </tr>
                        @endforeach
                    </tbody>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    
@endpush
