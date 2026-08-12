@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Editar Editorial'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->nombre . ' - Editar Editorial',
    ])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" id="editorial_form"
            action="{{ route('problemas.update_editorial', ['id' => $problema->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-book me-2"></i>Editorial del Problema: {{ $problema->nombre }}</h6>
                            <p class="text-xs text-secondary mb-0">Redacte la solución guiada o explicativa para orientar al estudiante.</p>
                        </div>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="body_editorial" id="body_editorial">
                    <label for="editor" class="form-control-label font-weight-bold text-sm mb-2">Contenido de la Editorial</label>
                    <div class="flex flex-col space-y-2 mb-3">
                        <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-save me-1"></i> Guardar Editorial</button>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
    <script type="module">
        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'markdown',
            placeholder: "La editorial es una forma para ayudar al estudiante para que pueda comprender el problema.",
            initialValue: `{{ $problema->body_editorial }}`,
        })
        document.querySelector('#editorial_form').addEventListener('submit', e => {
            e.preventDefault();
            document.querySelector('#body_editorial').value = editor.getMarkdown();
            e.target.submit();
        });
    </script>
@endpush
