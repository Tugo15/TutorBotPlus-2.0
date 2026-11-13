@php
    $breadcrumbsEditorial = [["nombre" => "Cursos", "route" => route("cursos.listado")]];
    if (!empty($id_curso)) {
        $breadcrumbsEditorial[] = ["nombre" => "Problemas", "route" => route('problemas.listado', ['id' => $id_curso])];
        $breadcrumbsEditorial[] = ["nombre" => $problema->nombre, "route" => route('problemas.ver', ['id_curso' => $id_curso, 'codigo' => $problema->codigo])];
    } else {
        $breadcrumbsEditorial[] = ["nombre" => $problema->nombre];
    }
    $breadcrumbsEditorial[] = ["nombre" => "Pistas"];
@endphp
@extends('layout_plataforma.app', ['title_html' => 'Pistas', 'title' => 'Pistas - '.$problema->nombre, 'breadcrumbs' => $breadcrumbsEditorial])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-header">
                <div class="d-flex d-flex justify-content-between">
                    <div class="ms-3 mt-2"><h6>Pistas</h6></div>
                    <div class="me-3"><a class="btn btn-primary" href="{{ !empty($id_curso) ? route('problemas.ver', ['id_curso'=>$id_curso, 'codigo'=>$problema->codigo]) : route('cursos.listado') }}" role="button">Volver</a></div>
                  </div>
            </div>
            <div class="card-body px-3 px-md-5" id="body_markdown">
                @if(empty(trim($problema->body_editorial ?? '')))
                    <div class="text-center py-4 text-secondary">
                        <i class="fa fa-lightbulb fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-0">Aún no hay pistas disponibles para este problema.</p>
                    </div>
                @else
                    @php
                        $bodyEditorialSanitized = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $problema->body_editorial ?? '');
                        $bodyEditorialSanitized = preg_replace_callback('/```[\s\S]*?```|`[^`]*`|(?<![\*\s\\\\])\*(?![\*\s])/u', function ($m) {
                            return $m[0] === '*' ? '\\*' : $m[0];
                        }, $bodyEditorialSanitized);
                    @endphp
                    {!! Str::markdown($bodyEditorialSanitized, [
                        'html_input' => 'strip',
                        'allow_unsafe_links' => false
                    ])!!}
                @endif
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var table = document.querySelector("#body_markdown table");
        if(table != null){
            var table_body = table.querySelector("tbody");
            if (table_body) {
                table.classList.add("table", "table-bordered", "table-hover", "mt-3");
                table.style.width = "auto";
                table_body.classList.add("table-group-divider");
            }
        }
        if (typeof window.renderFormulas === 'function') {
            window.renderFormulas(document.querySelector('#body_markdown') || document.body);
        }
    });
</script>
@endpush

