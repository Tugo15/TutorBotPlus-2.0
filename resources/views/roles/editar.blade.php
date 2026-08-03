@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Editar Rol'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Rol'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('roles.update', ['id'=>$rol->id]) }}" enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormEditar('{{$rol->nombre}}')" id="editarForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('roles.form')
                    <input type="submit" class="btn btn-primary" value="Guardar Cambios">
                    <a href="{{route('roles.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush