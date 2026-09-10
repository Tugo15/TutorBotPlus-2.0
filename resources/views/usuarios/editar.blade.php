@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Editar Usuario'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Usuario'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('usuarios.update', ['id'=>$user->id]) }}" enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormEditar('el usuario {{ $user->username }}')" id="editarForm">
            @csrf
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-user-edit me-2 text-primary"></i>Editar Usuario: {{ $user->username }}</h6>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @include('usuarios.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-dark me-2"><i class="fa fa-save me-1"></i> Guardar Cambios</button>
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