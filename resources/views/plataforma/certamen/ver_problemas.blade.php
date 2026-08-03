@extends('layout_plataforma.app', ['title_html' => $res_certamen->certamen->nombre, 'title' => 'Certamen - ' . $res_certamen->certamen->nombre, 'breadcrumbs' => [['nombre' => 'Evaluaciones', 'route' => route('certamenes.listado')], ['nombre' => $res_certamen->certamen->nombre, 'route' => route('certamenes.ver', ['id_certamen' => $res_certamen->certamen->id])], ['nombre' => 'Resolución']]])

@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3 row-cols-2">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger overflow-auto" style="height:40rem">
                    <div class="card-header">
                        Enunciado
                    </div>
                    <div class="card-body p-4 text-wrap" id="body_markdown">
                        <h4 id="titulo_problema">{{ $problemas[0]->nombre }}</h4>
                        <hr>
                        <div id="enunciado">
                            {!! Str::markdown($problemas[0]->body_problema, [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false,
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger px-5 py-3 mb-3" style="height:11rem">
                    <div class="d-flex justify-content-center">
                        @foreach ($problemas as $key => $problema)
                            <button type="button" id="problema_{{ $key }}"
                                class="btn btn-lg @if ($key == 0) active @endif me-2 @if (!isset($problema->resuelto)) btn-outline-secondary @elseif($problema->resuelto == true) btn-success @elseif($problema->resuelto == false) btn-danger @endif"
                                onclick="seleccion_problema({{ $key }})">{{ $key + 1 }}</button>
                        @endforeach
                    </div>
                    <h5 class="text-center my-3">Tiempo Restante: <strong id="timer">--:--:--</strong></h5>
                    <div class="d-flex justify-content-center">
                        <form action="{{ route('certamen.finalizar', ['token' => $res_certamen->token]) }}" method="POST"
                            id="finalizar_form" onsubmit="event.preventDefault();advertencia_finalizar()">
                            @csrf
                            <button class="btn btn-secondary btn-large align-self-center" type="submit">Finalizar</button>
                        </form>
                    </div>
                </div>
                <div class="card border-danger" style="height:28rem">
                    <div class="card-body px-3">
                        <div class="row px-5 mt-2">
                            <a class="btn btn-primary btn-sm btn-block @if ($problemas[0]->resuelto == true) disabled @endif"
                                href="{{ $problemas[0]->resolver_ruta }}" role="button" id="boton_resolver">
                                {{ $problemas[0]->resuelto == true ? 'Problema Resuelto' : 'Resolver Problema' }}</a>
                            <a class="btn btn-outline-secondary btn-sm btn-block mt-2" href="{{ $problemas[0]->pdf_ruta }}"
                                id="boton_pdf" target="_blank" role="button">Descargar PDF del Enunciado</a>
                        </div>
                        <hr>
                        <h6 class="ms-3 mt-3"><strong>Información:</strong></h6>
                        <ul class="list-group mt-3">
                            <li class="list-group-item"><strong>Puntos:</strong>
                                <span id="puntaje_total">{{ $problemas[0]->puntaje_total }}</span>
                            </li>
                            <li class="list-group-item"><strong>Límite de Tiempo:</strong>
                                <span
                                    id="tiempo_limite">{{ $problemas[0]->tiempo_limite ? $problemas[0]->tiempo_limite . ' s' : 'No definido' }}</span>
                            </li>
                            <li class="list-group-item"><strong>Límite de Memoria:</strong>
                                <span
                                    id="memoria_limite">{{ $problemas[0]->memoria_limite ? $problemas[0]->memoria_limite . ' KB' : 'No definido' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/2.1.0/showdown.min.js"></script>
    <script>
        const fecha_termino = new Date(@json($res_certamen->certamen->fecha_termino));
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

        function advertencia_finalizar() {
            Swal.fire({
                title: "¿Estás seguro de que quieres finalizar la evaluación?",
                icon: "warning",
                showDenyButton: true,
                confirmButtonText: "Si",
                denyButtonText: `No`
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('finalizar_form').submit();
                }
            });
        }

        function seleccion_problema(item) {
            if (item != id_problema_activo) {
                document.getElementById("problema_" + id_problema_activo).classList.remove('active');
                document.getElementById("problema_" + item).classList.add('active');
                id_problema_activo = item
                console.log(problemas[item])
                document.getElementById("enunciado").innerHTML = converter.makeHtml(problemas[item]["body_problema"]);
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
                if (problemas[item]["resuelto"] == true) {
                    boton_resolver.classList.add('disabled');
                    boton_resolver.innerHTML = "Problema Resuelto";

                } else {
                    boton_resolver.classList.remove('disabled');
                    boton_resolver.innerHTML = "Resolver Problema";
                    boton_resolver.setAttribute("href", problemas[item]["resolver_ruta"])
                    boton_pdf.setAttribute("href", problemas[item]["pdf_ruta"])
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
                            if (i == id_problema_activo && !boton_resolver.contains('disabled')) {
                                boton_resolver.classList.toggle('disabled');
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
