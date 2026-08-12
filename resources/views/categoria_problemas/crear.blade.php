@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Categoría de Problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Categoría de Problema'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('categorias.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-tags me-2"></i>Crear Nueva Categoría</h6>
                        <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('categoria_problemas.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-save me-1"></i> Crear Categoría</button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
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