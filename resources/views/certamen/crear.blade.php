@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Crear Evaluación'])
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Evaluación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('certamen.store') }}' enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormCrear()" id="crearForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-plus me-2 text-warning"></i>Crear Nueva Evaluación</h6>
                        <a href="{{ route('certamen.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('certamen.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-dark me-2"><i class="fa fa-save me-1"></i> Crear Evaluación</button>
                        <a href="{{ route('certamen.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
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
            initialValue: @json(old('descripcion', $certamen->descripcion ?? '')),
        });
        editor.on('change', () => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });
        document.querySelector('#crearForm').addEventListener('submit', e => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });

    </script>
@endpush
