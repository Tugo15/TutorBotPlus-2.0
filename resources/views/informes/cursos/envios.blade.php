@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Envios del Curso'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Envios realizado en el curso "'.$curso->nombre.'"'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Envios</h6>
                        <a href="{{route('informe.curso', ['id_curso' => $curso->id])}}" class="btn btn-outline-primary">Volver</a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @include('informes.componentes.tabla_envios')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
@endpush
