@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Casos de Prueba'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->codigo . ' - Casos de Prueba',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Caso de Prueba (SQL)</h6>
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
                    
                    <form action="{{route('casos_pruebas.set_sql', ['id'=>$problema->id])}}" method="POST">
                        @csrf
                        <div class="row mx-3">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="salidas" class="form-label">Salida esperada</label>
                                    <textarea class="form-control @error('salidas') is-invalid @enderror" id="salidas" name="salidas" rows="5">{{isset($caso)? old('salidas', $caso->salidas) : old('salidas')}}</textarea>
                                    @error('salidas')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="puntos" class="form-label">Puntos</label>
                                    <input type="number" class="form-control @error('puntos') is-invalid @enderror" id="puntos" name="puntos" placeholder="Ej. 5" value="{{isset($caso)? old('puntos', $caso->puntos) : old('puntos')}}">
                                    @error('puntos')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                </div>
                                <button class="btn btn-primary" type="submit">Guardar</button>
                                <button type="button" class="btn bg-outline-primary" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
                                    Ver Ejemplo
                                </button>
                                <a href="{{route('problemas.index')}}" class="btn bg-outline-primary">Volver</a>
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