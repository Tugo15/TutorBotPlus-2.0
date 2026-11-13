@php
    $certamenObj = (isset($res_certamen) && $res_certamen->certamen) ? $res_certamen->certamen : null;
    $certamenNombre = $certamenObj ? $certamenObj->nombre : 'Evaluación';
    $certamenId = $certamenObj ? $certamenObj->id : null;
    $fechaTerminoEfectivaObj = (isset($res_certamen) && $res_certamen->fecha_termino_efectiva) 
        ? $res_certamen->fecha_termino_efectiva 
        : ($certamenObj ? \Carbon\Carbon::parse($certamenObj->fecha_termino) : null);
    $certamenFechaTermino = $fechaTerminoEfectivaObj ? $fechaTerminoEfectivaObj->format('Y-m-d H:i:s') : null;
@endphp
@extends('layout_plataforma.app', ['title_html' => $certamenNombre, 'title' => 'Certamen - ' . $certamenNombre, 'breadcrumbs' => [['nombre' => 'Cursos', 'route' => route('cursos.listado')], ['nombre' => $certamenNombre, 'route' => $certamenId ? route('certamenes.ver', ['id_certamen' => $certamenId]) : route('cursos.listado')], ['nombre' => 'Resolución']]])

@push('css')
<style>
    #enunciado img, #body_markdown img, .markdown-body img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin: 1.5rem auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .btn.text-wrap, .btn {
        white-space: normal !important;
        word-break: break-word;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3 row-cols-2">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger shadow-sm h-100" style="min-height:40rem">
                    <div class="card-header">
                        Enunciado
                    </div>
                    <div class="card-body p-4 text-wrap" id="body_markdown">
                        <h4 id="titulo_problema">{{ $problemas[0]->nombre }}</h4>
                        <hr>
                        <div id="enunciado">
                            @php
                                $bodyProblemaSanitized = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $problemas[0]->body_problema ?? '');
                            @endphp
                            {!! Str::markdown($bodyProblemaSanitized, [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => true,
                            ]) !!}

                            @php
                                $casos_ejemplo_0 = isset($problemas[0]->casos_de_prueba) ? $problemas[0]->casos_de_prueba : $problemas[0]->casos_de_prueba()->where('ejemplo', true)->orderBy('id', 'asc')->get();
                            @endphp

                            @if(count($casos_ejemplo_0) > 0)
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-vials me-2"></i>Ejemplos de Entrada y Salida</h6>
                                    <div class="row">
                                        @foreach($casos_ejemplo_0 as $caso)
                                            <div class="col-12 mb-3">
                                                <div class="card bg-gray-100 border shadow-none">
                                                    <div class="card-header py-1 px-3 bg-gray-200">
                                                        <span class="text-xs font-weight-bold text-dark">Ejemplo #{{ $loop->iteration }}</span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <div class="row">
                                                            @if(!is_null($caso->entradas) && trim($caso->entradas) !== '')
                                                                <div class="col-md-6 mb-2 mb-md-0">
                                                                    <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Entrada</span>
                                                                    <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->entradas }}</pre>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                                    <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
                                                                </div>
                                                            @else
                                                                <div class="col-12">
                                                                    <span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span>
                                                                    <pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">{{ $caso->salidas }}</pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger p-3 px-sm-4 mb-3" style="min-height: 11rem; height: auto;">
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        @foreach ($problemas as $key => $problema)
                            <button type="button" id="problema_{{ $key }}"
                                class="btn btn-lg @if ($key == 0) active @endif @if (!isset($problema->resuelto)) btn-outline-secondary @elseif($problema->resuelto == true) btn-success @elseif($problema->resuelto == false) btn-danger @endif"
                                onclick="seleccion_problema({{ $key }})">{{ $key + 1 }}</button>
                        @endforeach
                    </div>
                    <h5 class="text-center my-3">Tiempo Restante: <strong id="timer">--:--:--</strong></h5>
                    <div class="d-flex justify-content-center">
                        <form action="{{ route('certamen.finalizar', ['token' => $res_certamen->token]) }}" method="POST"
                            id="finalizar_form">
                            @csrf
                            <button class="btn btn-danger btn-large align-self-center text-wrap px-4 py-2 font-weight-bold shadow-sm" type="button" id="btnFinalizarCertamen" onclick="confirmarFinalizarCertamen()">
                                <i class="fa fa-flag-checkered me-1"></i> Finalizar Evaluación
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card border-danger shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-grid gap-2 px-2 mt-1">
                            <a class="btn btn-primary btn-sm text-wrap"
                                href="{{ $problemas[0]->resolver_ruta }}" role="button" id="boton_resolver">
                                <i class="fa {{ $problemas[0]->resuelto == true ? 'fa-redo' : 'fa-code' }} me-1"></i>
                                {{ $problemas[0]->resuelto == true ? 'Volver a Resolver' : 'Resolver Problema' }}</a>
                            <a class="btn btn-outline-secondary btn-sm text-wrap" href="{{ $problemas[0]->pdf_ruta }}"
                                id="boton_pdf" target="_blank" role="button">
                                <i class="fa fa-file-pdf me-1"></i> Descargar PDF del Enunciado
                            </a>
                        </div>
                        <hr class="my-3">
                        <h6 class="px-2 font-weight-bold text-dark mb-2"><i class="fa fa-info-circle me-1 text-primary"></i> Información:</h6>
                        <ul class="list-group list-group-flush border rounded shadow-none mx-1">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <strong>Puntos:</strong>
                                <span class="badge bg-primary rounded-pill" id="puntaje_total">{{ $problemas[0]->puntaje_total }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <strong>Límite de Tiempo:</strong>
                                <span id="tiempo_limite">{{ $problemas[0]->tiempo_limite ? $problemas[0]->tiempo_limite . ' s' : 'No definido' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <strong>Límite de Memoria:</strong>
                                <span id="memoria_limite">{{ $problemas[0]->memoria_limite ? $problemas[0]->memoria_limite . ' KB' : 'No definido' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/2.1.0/showdown.min.js"></script>
    <script>
        // Protección contra caché del navegador al presionar "Atrás" (BFCache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2) || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });

        const fecha_termino = new Date(@json($certamenFechaTermino));
        var problemas = @json($problemas);
        var id_problema_activo = 0;
        showdown.setOption('tables', 'true')
        showdown.setOption('tablesHeaderId', 'true')
        showdown.setOption('moreStyling', 'true')
        showdown.setFlavor('github');
        var converter = new showdown.Converter();

        function style_table() {
            var table = document.querySelectorAll("#body_markdown table")
            if (table != null) {
                for (var i = 0; i < table.length; i++) {
                    var table_body = table[i].querySelector("tbody")
                    table[i].classList.add("table")
                    table[i].classList.add("table-bordered")
                    table[i].classList.add("table-hover")
                    table[i].classList.add("mt-3")
                    table[i].style.width = "auto"
                    table_body.classList.add("table-group-divider")
                }

            }
        }
        style_table()

        function confirmarFinalizarCertamen() {
            const titulo = "¿Estás seguro de que deseas finalizar la evaluación?";
            const textoHtml = "<p class='text-secondary text-sm mb-0'>Una vez finalizada, se guardará tu progreso y se registrarán tus soluciones y puntajes actuales.<br><br><strong class='text-danger'><i class='fa fa-exclamation-triangle me-1'></i> Atención: No podrás volver a ingresar a resolver problemas ni modificar tus respuestas.</strong></p>";

            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                Swal.fire({
                    title: titulo,
                    html: textoHtml,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "<i class='fa fa-check me-1'></i> Sí, finalizar evaluación",
                    cancelButtonText: "<i class='fa fa-times me-1'></i> Cancelar y seguir resolviendo",
                    reverseButtons: true,
                    focusCancel: true,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Finalizando evaluación...',
                            text: 'Por favor espera mientras se procesa el cierre de tu intento.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        document.getElementById('finalizar_form').submit();
                    }
                });
            } else {
                if (window.confirm(titulo + "\n\nUna vez finalizada, se registrarán tus soluciones y puntajes actuales. No podrás volver a ingresar ni modificar tus respuestas.")) {
                    document.getElementById('finalizar_form').submit();
                }
            }
        }
        function advertencia_finalizar() {
            confirmarFinalizarCertamen();
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;")
                       .replace(/</g, "&lt;")
                       .replace(/>/g, "&gt;")
                       .replace(/"/g, "&quot;")
                       .replace(/'/g, "&#039;");
        }

        function seleccion_problema(item) {
            if (item != id_problema_activo) {
                document.getElementById("problema_" + id_problema_activo).classList.remove('active');
                document.getElementById("problema_" + item).classList.add('active');
                id_problema_activo = item
                console.log(problemas[item])
                let mdRaw = problemas[item]["body_problema"] || '';
                let mdClean = mdRaw.replace(/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/g, '![$1]($2)');
                let htmlEnunciado = converter.makeHtml(mdClean);
                if (problemas[item]["casos_de_prueba"] && problemas[item]["casos_de_prueba"].length > 0) {
                    htmlEnunciado += '<div class="mt-4 pt-3 border-top"><h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-vials me-2"></i>Ejemplos de Entrada y Salida</h6><div class="row">';
                    problemas[item]["casos_de_prueba"].forEach((caso, idx) => {
                        htmlEnunciado += '<div class="col-12 mb-3"><div class="card bg-gray-100 border shadow-none"><div class="card-header py-1 px-3 bg-gray-200"><span class="text-xs font-weight-bold text-dark">Ejemplo #' + (idx + 1) + '</span></div><div class="card-body p-3"><div class="row">';
                        if (caso.entradas && caso.entradas.trim() !== '') {
                            htmlEnunciado += '<div class="col-md-6 mb-2 mb-md-0"><span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Entrada</span><pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">' + escapeHtml(caso.entradas) + '</pre></div>';
                            htmlEnunciado += '<div class="col-md-6"><span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span><pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">' + escapeHtml(caso.salidas) + '</pre></div>';
                        } else {
                            htmlEnunciado += '<div class="col-12"><span class="text-xs font-weight-bold text-uppercase d-block mb-1 text-secondary">Salida Esperada</span><pre class="bg-white p-2 rounded border mb-0 text-dark" style="font-family: monospace; white-space: pre-wrap; font-size: 0.85rem;">' + escapeHtml(caso.salidas) + '</pre></div>';
                        }
                        htmlEnunciado += '</div></div></div></div>';
                    });
                    htmlEnunciado += '</div></div>';
                }
                document.getElementById("enunciado").innerHTML = htmlEnunciado;
                if (typeof window.renderFormulas === 'function') {
                    window.renderFormulas(document.getElementById("enunciado"));
                }
                document.getElementById("titulo_problema").innerHTML = problemas[item]["nombre"];

                document.getElementById("puntaje_total").innerHTML = problemas[item]["puntaje_total"];
                if (problemas[item]["tiempo_limite"] == null) {
                    document.getElementById("tiempo_limite").innerHTML = "No Definido";
                } else {
                    document.getElementById("tiempo_limite").innerHTML = problemas[item]["tiempo_limite"] + "s";
                }

                if (problemas[item]["memoria_limite"] == null) {
                    document.getElementById("memoria_limite").innerHTML = "No Definido";
                } else {
                    document.getElementById("memoria_limite").innerHTML = problemas[item]["memoria_limite"] + "KB";
                }
                let boton_resolver = document.getElementById("boton_resolver");
                let boton_pdf = document.getElementById("boton_pdf");
                boton_resolver.classList.remove('disabled');
                boton_resolver.setAttribute("href", problemas[item]["resolver_ruta"]);
                boton_pdf.setAttribute("href", problemas[item]["pdf_ruta"]);

                if (problemas[item]["resuelto"] == true) {
                    boton_resolver.innerHTML = '<i class="fa fa-redo me-1"></i> Volver a Resolver';
                } else {
                    boton_resolver.innerHTML = '<i class="fa fa-code me-1"></i> Resolver Problema';
                }
                style_table()
            }
        }
        var timer_certamen = setInterval(function() {

            var now = new Date().getTime();

            var distancia = fecha_termino - now;

            var horas = Math.floor((distancia / (1000 * 60 * 60)));
            var minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
            var segundos = Math.floor((distancia % (1000 * 60)) / 1000);
            document.getElementById("timer").innerHTML = ("0" + horas).slice(-2) + ":" + ("0" + minutos).slice(-2) +
                ":" + ("0" + segundos).slice(-2);
            if (distancia < 1800000) {
                document.getElementById("timer").classList.add('text-danger');
            }
            if (distancia < 0) {
                clearInterval(timer_certamen);
                document.getElementById("timer").innerHTML = "Finalizado";
                document.getElementById('finalizar_form').submit();
            }
        }, 1000);

        var actualizar_informacion = setInterval(function() {

            fetch("{{ route('certamenes.update_data', ['token' => $res_certamen->token]) }}", {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(result) {
                    problemas = result;
                    for (var i = 0; i < result.length; i++) {
                        var button_problema = document.getElementById('problema_' + i);
                        button_problema.classList.remove('btn-success', 'btn-danger', 'btn-outline-secondary')

                        if (result[i]["resuelto"] == true) {
                            button_problema.classList.add('btn-success');
                            if (i == id_problema_activo) {
                                let boton_resolver = document.getElementById("boton_resolver");
                                if (boton_resolver) {
                                    boton_resolver.innerHTML = '<i class="fa fa-redo me-1"></i> Volver a Resolver';
                                }
                            }
                        } else if (result[i]["resuelto"] == false) {
                            button_problema.classList.add('btn-danger');
                        } else {
                            button_problema.classList.add('btn-outline-secondary');
                        }

                    }
                })
                .catch(function(error) {
                    clearInterval(actualizar_informacion);
                });
        }, 4000);
    </script>
@endpush
