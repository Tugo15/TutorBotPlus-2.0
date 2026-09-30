@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Rol'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Rol'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('roles.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;">
                            <a href="{{ route('roles.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-shield-alt me-2 text-info"></i>Crear Nuevo Rol</h6>
                            <p class="text-xs text-secondary mb-0">Defina un nuevo rol y asigne sus permisos asociados.</p>
                        </div>
                        <div class="d-none d-lg-block" style="flex: 1;"></div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('roles.form')
                    <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-dark mb-0"><i class="fa fa-save me-1"></i> Crear Rol</button>
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