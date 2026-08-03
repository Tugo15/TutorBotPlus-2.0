@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Crear Evaluación'])
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Evaluación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('certamen.store') }}' enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('certamen.form')
                    <input type="submit" class="btn btn-primary" value="Crear">
                    <a href="{{route('certamen.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
@php
    $fecha_inicio = isset($certamen)? old('fecha_inicio', $certamen->fecha_inicio) : old('fecha_inicio');
    $fecha_termino = isset($certamen)? old('fecha_termino', $certamen->fecha_termino) : old('fecha_termino');   
@endphp
@push('js')
<script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
    <script type="module">
        const set_fecha_inicio = @json($fecha_inicio);
        const set_fecha_termino = @json($fecha_termino);
        const fecha_inicio = flatpickr("#fecha_inicio", {enableTime: true,
            dateFormat: "d-m-Y H:i", minDate: new Date(),}); // flatpickr
        const fecha_termino = flatpickr("#fecha_termino", {enableTime: true,
            dateFormat: "d-m-Y H:i", minDate: new Date(),}); // flatpickr
            fecha_inicio.setDate(set_fecha_inicio)
            fecha_termino.setDate(set_fecha_termino)
        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'markdown',
            placeholder: 'Ingrese el enunciado del problema',
            initialValue: `{{ isset($certamen) ? old('descripcion', $certamen->descripcion) : old('descripcion') }}`,
        })
        document.querySelector('#crearForm').addEventListener('submit', e => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });

    </script>
@endpush
