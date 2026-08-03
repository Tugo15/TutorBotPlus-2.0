@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Editar Lenguaje de Programación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Lenguaje de Programación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('lenguaje_programacion.update', ['id'=>$lenguaje->id]) }}" onsubmit="event.preventDefault();submitFormEditar('{{'el lenguaje'.$lenguaje->nombre}}')" id="editarForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('lenguaje_programacion.form')
                    <input type="submit" class="btn btn-primary" value="Guardar Cambios">
                    <a href="{{route('lenguaje_programacion.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush