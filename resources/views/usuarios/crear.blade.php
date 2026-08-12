@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Usuario'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Usuario'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('usuarios.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-user-plus me-2"></i>Crear Nuevo Usuario</h6>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('usuarios.form')
                    <div class="mt-4">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-save me-1"></i> Crear Usuario</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
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