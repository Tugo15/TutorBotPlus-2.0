@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Usuario'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Usuario'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('usuarios.store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('usuarios.form')
                    <input type="submit" class="btn btn-primary" value="Crear">
                    <a href="{{route('usuarios.index')}}" class="btn btn-outline-primary">Volver</a>
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