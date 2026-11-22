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
    <div class="container-fluid px-3 px-md-4">
        <div class="card border-danger">
            <div class="card-header d-flex justify-content-between align-items-center px-3 px-md-4 py-2">
                <h6 class="mb-0"><strong>Pistas</strong></h6>
                <a class="btn btn-sm btn-primary mb-0" href="{{ !empty($id_curso) ? route('problemas.ver', ['id_curso'=>$id_curso, 'codigo'=>$problema->codigo]) : route('cursos.listado') }}" role="button">Volver</a>
            </div>
            <div class="card-body px-3 px-md-4 py-4" id="body_markdown">
                @if(empty(trim($problema->body_editorial ?? '')))
                    <div class="text-center py-4 text-secondary">
                        <i class="fa fa-lightbulb fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-0">Aún no hay pistas disponibles para este problema.</p>
                    </div>
                @else
                    {!! \App\Support\MarkdownRenderer::render($problema->body_editorial ?? '') !!}
                @endif
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var container = document.querySelector('#body_markdown');
        var tables = document.querySelectorAll("#body_markdown table");
        if (tables != null) {
            for (var i = 0; i < tables.length; i++) {
                var table_body = tables[i].querySelector("tbody");
                if (table_body) {
                    tables[i].classList.add("table", "table-bordered", "table-hover", "mt-3");
                    tables[i].style.width = "auto";
                    table_body.classList.add("table-group-divider");
                }
            }
        }
        if (container) {
            container.querySelectorAll('pre > code.language-katex, pre > code.language-math, pre > code.language-latex').forEach(function(codeEl) {
                var pre = codeEl.parentElement;
                var div = document.createElement('div');
                div.className = 'katex-block my-3';
                div.textContent = '$$' + (codeEl.textContent || '').trim() + '$$';
                pre.replaceWith(div);
            });
        }
        if (typeof window.renderFormulas === 'function') {
            window.renderFormulas(container || document.body);
        }
    });
</script>
@endpush

