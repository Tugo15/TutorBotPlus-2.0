@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Casos de Prueba'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->codigo . ' - Casos de Prueba',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;">
                            <a href="{{ route('problemas.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-database me-2 text-warning"></i>Caso de Prueba (SQL): {{ $problema->nombre }}</h6>
                            <p class="text-xs text-secondary mb-0">Gestión de la salida esperada para consultas SQL.</p>
                        </div>
                        <div class="d-none d-lg-flex justify-content-end" style="flex: 1;"></div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('error') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('success') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <form action="{{route('casos_pruebas.set_sql', ['id'=>$problema->id])}}" method="POST">
                        @csrf
                        <div class="row mx-3 mt-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="salidas" class="form-label font-weight-bold text-xs text-dark">Salida esperada (Resultado de la consulta SQL)</label>
                                    <textarea class="form-control font-monospace text-xs @error('salidas') is-invalid @enderror" id="salidas" name="salidas" rows="6" placeholder="Salida esperada del query SQL...">{{isset($caso)? old('salidas', $caso->salidas) : old('salidas')}}</textarea>
                                    @error('salidas')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="puntos" class="form-label font-weight-bold text-xs text-dark">Puntos</label>
                                    <input type="number" step="any" class="form-control form-control-sm @error('puntos') is-invalid @enderror" id="puntos" name="puntos" placeholder="Ej. 100" value="{{isset($caso)? old('puntos', $caso->puntos) : old('puntos')}}">
                                    @error('puntos')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                </div>
                                <div class="d-flex flex-column gap-2 pt-2">
                                    <button class="btn btn-sm btn-dark mb-0" type="submit"><i class="fa fa-save me-1"></i> Guardar Caso SQL</button>
                                    <button type="button" class="btn btn-sm btn-outline-info mb-0" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
                                        <i class="fa fa-eye me-1"></i> Ver Ejemplo
                                    </button>
                                    <a href="{{route('problemas.index')}}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver a Problemas</a>
                                </div>
                            </div>
                        </div>
                    </form>
                                
                </div>
            </div>
        </div>
    </div>
    @include('problemas.casos_pruebas.ejemplo_sql')
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
@endpush