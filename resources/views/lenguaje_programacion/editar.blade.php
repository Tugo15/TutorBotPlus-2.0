@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Editar Lenguaje de Programación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Lenguaje de Programación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('lenguaje_programacion.update', ['id'=>$lenguaje->id]) }}" onsubmit="event.preventDefault();submitFormEditar('{{'el lenguaje'.$lenguaje->nombre}}')" id="editarForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-code me-2"></i>Editar Lenguaje: {{ $lenguaje->nombre }}</h6>
                        <a href="{{ route('lenguaje_programacion.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('lenguaje_programacion.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-save me-1"></i> Guardar Cambios</button>
                        <a href="{{ route('lenguaje_programacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush