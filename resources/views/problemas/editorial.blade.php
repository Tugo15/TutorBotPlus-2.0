@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Editar Pistas'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->nombre . ' - Editar Pistas',
    ])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" id="editorial_form"
            action="{{ route('problemas.update_editorial', ['id' => $problema->id]) }}" enctype="multipart/form-data">
            @csrf
            @if(isset($id_curso) && $id_curso)
                <input type="hidden" name="id_curso" value="{{ $id_curso }}">
            @elseif(request('id_curso'))
                <input type="hidden" name="id_curso" value="{{ request('id_curso') }}">
            @endif
            <div class="card shadow-xs border">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;">
                            <a href="{{ (isset($id_curso) && $id_curso) || request('id_curso') ? route('problemas.index', ['id_curso' => $id_curso ?? request('id_curso')]) : route('problemas.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-book me-2 text-warning"></i>Pistas del Problema: {{ $problema->nombre }}</h6>
                            <p class="text-xs text-secondary mb-0">Redacte las pistas o guía explicativa para orientar al estudiante.</p>
                        </div>
                        <div class="d-none d-lg-block" style="flex: 1;"></div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <input type="hidden" name="body_editorial" id="body_editorial">
                    <label for="editor" class="form-control-label font-weight-bold text-sm mb-2">Contenido de las Pistas</label>
                    <div class="flex flex-col space-y-2 mb-3">
                        <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" style="position: relative; z-index: 9999;"></div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-dark mb-0"><i class="fa fa-save me-1"></i> Guardar Pistas</button>
                    </div>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/tui-editor-image-handler.js') }}"></script>
    <script type="module">
        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'wysiwyg',
            previewStyle: 'vertical',
            placeholder: "Las pistas son una forma para ayudar al estudiante para que pueda comprender el problema.",
            initialValue: @json($problema->body_editorial ?? ''),
            extendedAutolinks: true,
            hooks: {
                addImageBlobHook: (blob, callback) => {
                    const formData = new FormData();
                    formData.append('image', blob);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route('problemas.upload_imagen') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        callback(data.relative_url || data.url, blob.name || 'Diagrama');
                    })
                    .catch(err => {
                        console.error('Error al subir imagen al servidor:', err);
                        alert('Error al subir la imagen al servidor. Por favor verifique el archivo.');
                    });
                }
            },
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

        if (typeof window.attachImageUploadToEditor === 'function') {
            window.attachImageUploadToEditor(editor, '#editor', '{{ route('problemas.upload_imagen') }}', '{{ csrf_token() }}');
        }

        editor.on('change', () => {
            document.querySelector('#body_editorial').value = editor.getMarkdown();
        });

        document.querySelector('#editorial_form').addEventListener('submit', e => {
            e.preventDefault();
            document.querySelector('#body_editorial').value = editor.getMarkdown();
            e.target.submit();
        });


    </script>
@endpush
