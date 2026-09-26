@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Envios del Curso'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Envios realizado en el curso "'.$curso->nombre.'"'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-1">
                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-primary"></i>Envíos del Curso</h6>
                            <p class="text-xs text-secondary mb-0">Listado de todos los envíos realizados en el curso.</p>
                        </div>
                        <a href="{{route('informe.curso', ['id_curso' => $curso->id])}}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
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
