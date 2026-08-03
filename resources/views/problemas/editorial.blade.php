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
            <div class="card">
                <div class="card-body">
                    <input type="hidden" name="body_editorial" id="body_editorial">
                    <label for="editor" class="text-gray-600 font-semibold">Editorial</label>
                    <div class="flex flex-col space-y-2 mb-3">
                        <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Editar">
                    <a href="{{route('problemas.index')}}" class="btn btn-outline-primary">Volver</a>

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
