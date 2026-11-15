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
                        $mathBlocks = [];
                        $bodyEditorialSanitized = preg_replace_callback('/```[\s\S]*?```|`[^`\r\n]*`|\$\$[\s\S]+?\$\$|(?<!\$)\$(?!\$)([^\$\r\n]+?)\$(?!\$)|(?<=[\p{L}\p{N}])\*(?=[\p{L}\p{N}])/u', function ($m) use (&$mathBlocks) {
                            if ($m[0] === '*') {
                                return '\\*';
                            }
                            if (str_starts_with($m[0], '$')) {
                                $key = 'TBMATHPLACEHOLDER' . count($mathBlocks) . 'END';
                                $mathBlocks[$key] = e($m[0]);
                                return $key;
                            }
                            return $m[0];
                        }, $bodyEditorialSanitized);
                        $renderedEditorialHtml = Str::markdown($bodyEditorialSanitized, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false
                        ]);
                        if (!empty($mathBlocks)) {
                            $renderedEditorialHtml = strtr($renderedEditorialHtml, $mathBlocks);
                        }
                    @endphp
                    {!! $renderedEditorialHtml !!}
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

