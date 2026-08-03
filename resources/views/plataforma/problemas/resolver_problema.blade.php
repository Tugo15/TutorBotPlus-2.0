@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre . ' - Resolver', 'breadcrumbs'=>[["nombre"=>"Cursos", "route"=>route("cursos.listado")],["nombre"=>"Problemas", "route"=>route('problemas.listado', ['id'=>$id_curso])],["nombre"=>$problema->nombre,"route"=>route('problemas.ver', ['codigo'=>$problema->codigo, 'id_curso'=>$id_curso])], ["nombre"=>"Resolver"]]])
@section('content')
    <div class="container-fluid px-4 pb-4">
        @include('components.alert')
        <div class="row">
            <div class="col-sm col-xs-12">
                
                @include('plataforma.problemas.componentes.card_editor')
            </div>
            <div class="col-sm-4 col-xs-12">
                @if(isset($res_certamen))
                @include('plataforma.problemas.componentes.card_timer_certamen')
                @endif
                @include('plataforma.problemas.componentes.card_enviar_solucion')
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/ace-builds/src-min/ace.js') }}" type="text/javascript" charset="utf-8"></script>
    <script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script>
    <script>
        var editor = ace.edit("editor");
        var formSubmited = false
        const lenguajes = {
            48: 'c_cpp',
            52: 'c_cpp',
            49: 'c_cpp',
            54: 'c_cpp',
            50: 'c_cpp',
            53: 'c_cpp',
            51: 'csharp',
            91: 'java',
            70: 'python',
            71: 'python',
            92: 'python',
            82: 'sql',
        }
        var set_lenguaje = lenguajes[{{ strtolower($last_envio->ProblemaLenguaje->lenguaje->codigo) }}]
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/" + set_lenguaje);
        editor.setOptions({
            fontSize: "10pt"
        })


        function change_language(item) {
            editor.session.setMode("ace/mode/" + lenguajes[item.value]);
           document.querySelector('#lenguaje_save').value = item.options[item.selectedIndex].id;
        }
        document.querySelector('#evaluacion_form').addEventListener('submit', e => {
            e.preventDefault();
            formSubmited = true
            submitCodigo()
        });
        document.querySelector('#guardarForm').addEventListener('submit', e => {
            document.querySelector('#codigo_save').value = editor.getValue();
            formSubmited = true
            document.getElementById('guardarForm').submit();
        });
        window.addEventListener("beforeunload", function (e) {
            if(!formSubmited){
                e.preventDefault();
                e.returnValue = '';
            }
            
        });
    </script>
@endpush
