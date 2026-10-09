@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Usuario'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Usuario'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('usuarios.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="row align-items-center pb-2 gy-2">
                        <div class="col-12 col-lg d-flex justify-content-center justify-content-lg-start">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="col-12 col-lg-auto text-center">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-user-plus me-2 text-primary"></i>Crear Nuevo Usuario</h6>
                            <p class="text-xs text-secondary mb-0">Complete el formulario para registrar un nuevo usuario.</p>
                        </div>
                        <div class="col-12 col-lg"></div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('usuarios.form')
                    <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-dark mb-0"><i class="fa fa-save me-1"></i> Crear Usuario</button>
                    </div>
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script>
    <script src="{{asset('assets/js/rutFormatting.js')}}"></script> 
@endpush