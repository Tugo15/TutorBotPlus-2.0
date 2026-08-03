@extends('layout_plataforma.app', ['title_html' => 'Pistas', 'title' => 'Pistas - '.$problema->nombre, 'breadcrumbs'=>[["nombre"=>"Cursos", "route"=>route("cursos.listado")],["nombre"=>"Problemas", "route"=>route('problemas.listado', ['id'=>$id_curso])],["nombre"=>$problema->nombre, "route"=>route('problemas.ver', ['id_curso'=>$id_curso, 'codigo'=>$problema->codigo])], ["nombre"=>"Editorial"]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-header">
                <div class="d-flex d-flex justify-content-between">
                    <div class="ms-3 mt-2"><h6>Pistas</h6></div>
                    <div class="me-3"><a class="btn btn-primary" href="{{route('problemas.ver', ['id_curso'=>$id_curso, 'codigo'=>$problema->codigo])}}" role="button">Volver</a></div>
                  </div>
            </div>
            <div class="card-body px-5" #body_markdown>
                {!! Str::markdown($problema->body_editorial, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false
                ])!!}
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    var table = document.querySelector("#body_markdown table")
    if(table != null){
        var table_body = table.querySelector("tbody")
        table.classList.add("table")
        table.classList.add("table-bordered")
        table.classList.add("table-hover")
        table.classList.add("mt-3") 
        table.style.width = "auto"
        table_body.classList.add("table-group-divider")
    }
</script>
@endpush
