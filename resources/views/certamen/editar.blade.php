@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Editar Evaluación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Evaluación'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('certamen.update', ['id' => $certamen->id]) }}"
            enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormEditar('{{'el certamen '.$certamen->nombre}}')" id="editarForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-check me-2 text-warning"></i>Editar Evaluación: {{ $certamen->nombre }}</h6>
                        <a href="{{ route('certamen.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('certamen.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-dark me-2"><i class="fa fa-save me-1"></i> Guardar Cambios</button>
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
            dateFormat: "d-m-Y H:i",defaultDate: set_fecha_inicio, minDate: new Date(),}); // flatpickr
        const fecha_termino = flatpickr("#fecha_termino", {enableTime: true,
            dateFormat: "d-m-Y H:i",defaultDate:set_fecha_termino, minDate: new Date(),}); // flatpickr

        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'markdown',
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

        document.querySelector('#editarForm').addEventListener('submit', e => {
            document.querySelector('#descripcion').value = editor.getMarkdown();
        });



    </script>
@endpush

