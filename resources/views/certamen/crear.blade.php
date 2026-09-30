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
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;">
                            <a href="{{ route('certamen.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-plus me-2 text-warning"></i>Crear Nueva Evaluación</h6>
                            <p class="text-xs text-secondary mb-0">Configure los detalles, fechas y descripción de la evaluación.</p>
                        </div>
                        <div class="d-none d-lg-block" style="flex: 1;"></div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('certamen.form')
                    <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-dark mb-0"><i class="fa fa-save me-1"></i> Crear Evaluación</button>
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
            initialEditType: 'wysiwyg',
            previewStyle: 'vertical',
            placeholder: 'Ingrese el enunciado del problema',
            initialValue: @json(old('descripcion', $certamen->descripcion ?? '')),
            extendedAutolinks: true,
            customHTMLRenderer: {
                codeBlock(node, { innerHTML }) {
                    if (node.info === 'katex' || node.info === 'math' || node.info === 'latex') {
                        try {
                            const html = window.katex ? window.katex.renderToString(innerHTML, { displayMode: true, throwOnError: false }) : innerHTML;
                            return {
                                type: 'html',
                                content: `<div class="katex-block my-2 text-center">${html}</div>`
                            };
                        } catch (e) {
                            return { type: 'html', content: `<pre>${innerHTML}</pre>` };
                        }
                    }
                }
            }
        });

        if (typeof window.attachKaTeXToEditor === 'function') {
            window.attachKaTeXToEditor(editor, '#editor');
        }

        editor.on('change', () => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });

        document.querySelector('#crearForm').addEventListener('submit', e => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });



    </script>
@endpush
