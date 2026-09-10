@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=> "Crear Lenguaje de Programación"])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Lenguaje de Programación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('lenguaje_programacion.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-warning"></i>Crear Nuevo Lenguaje</h6>
                        <a href="{{ route('lenguaje_programacion.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @include('lenguaje_programacion.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-dark me-2"><i class="fa fa-save me-1"></i> Crear Lenguaje</button>
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