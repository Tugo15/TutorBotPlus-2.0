@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Editar Problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Problema'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('problemas.update', ['id' => $problema->id]) }}"
            enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormEditar('{{'el problema '.$problema->nombre}}')" id="editarForm">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-pencil-alt me-2 text-primary"></i>Editar Problema: {{ $problema->nombre }}</h6>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('problemas.form')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-dark me-2"><i class="fa fa-save me-1"></i> Guardar Cambios</button>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
    <script type="module">
        const checkbox = document.getElementById('sql')
        const lenguajeCheckboxes = document.querySelectorAll(".lenguaje-checkbox");
        const checkbox_fecha_inicio = document.getElementById('set_fecha_inicio')
        const checkbox_fecha_termino = document.getElementById('set_fecha_termino')
        const sql_file = document.getElementById("sql_file");
        const archivos_adicionales = document.getElementById("archivos_adicionales")
        const f_ini_val = @json(old('fecha_inicio', $problema->fecha_inicio));
        const f_ter_val = @json(old('fecha_termino', $problema->fecha_termino));
        const fecha_inicio = flatpickr("#fecha_inicio", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: f_ini_val ? f_ini_val : null
        }); 
        const fecha_termino = flatpickr("#fecha_termino", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: f_ter_val ? f_ter_val : null
        });
        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            placeholder: 'Ingrese el enunciado del problema',
            initialValue: @json(old('body_problema', $problema->body_problema ?? '')),
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
            document.querySelector('#body_problema').value = editor.getMarkdown();
        });

        document.querySelector('#editarForm').addEventListener('submit', e => {
            document.querySelector('#body_problema').value = editor.getMarkdown();
        });



        checkbox.addEventListener('change', (event) => {
            const isChecked = event.currentTarget.checked;
            lenguajeCheckboxes.forEach(cb => {
                cb.disabled = isChecked;
                const card = cb.closest('.selection-card');
                if (card) card.classList.toggle('disabled', isChecked);
            });
            if (isChecked) {
                sql_file.classList.remove("d-none");
            } else {
                sql_file.classList.add("d-none");
                if (archivos_adicionales) archivos_adicionales.value = "";
            }
        })

        checkbox_fecha_inicio.addEventListener('change', (event) => {
            if (event.currentTarget.checked) {
                document.getElementById('fecha_inicio').disabled = false;
                let elementos = document.querySelectorAll('.fecha_inicio_class')
                for (var i = 0; i < elementos.length; ++i) {
                    elementos[i].classList.remove('d-none');
                }
            } else {
                let elementos = document.querySelectorAll('.fecha_inicio_class')
                for (var i = 0; i < elementos.length; ++i) {
                    elementos[i].classList.add('d-none');
                }
                document.getElementById('fecha_inicio').disabled = true;
            }
        })

        checkbox_fecha_termino.addEventListener('change', (event) => {
            if (event.currentTarget.checked) {
                document.getElementById('fecha_termino').disabled = false;
                let elementos = document.querySelectorAll('.fecha_termino_class')
                for (var i = 0; i < elementos.length; ++i) {
                    elementos[i].classList.remove('d-none');
                }
            } else {
                document.getElementById('fecha_termino').disabled = true;
                let elementos = document.querySelectorAll('.fecha_termino_class')
                for (var i = 0; i < elementos.length; ++i) {
                    elementos[i].classList.add('d-none');
                }
            }
        })
    </script>
@endpush
