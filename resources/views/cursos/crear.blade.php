@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Curso'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('cursos.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('cursos.form')
                    <input type="submit" class="btn btn-primary" value="Crear">
                    <a href="{{route('cursos.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush
