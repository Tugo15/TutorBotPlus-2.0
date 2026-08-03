@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre, 'breadcrumbs' => [['nombre' => 'Cursos', 'route' => route('cursos.listado')], ['nombre' => 'Problemas', 'route' => route('problemas.listado', ['id' => $id_curso])], ['nombre' => $problema->nombre]]])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row mb-3">
            <div class="col-sm-8 col-xs-12">
                <div class="card border-danger overflow-auto" style="height:40rem">
                    <div class="card-header">
                        Enunciado
                    </div>
                    <div class="card-body p-4 text-wrap" id="body_markdown">
                        {!! Str::markdown($problema->body_problema, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger" style="height:40rem">
                    <div class="card-body px-3">
                        <div class="row px-5">
                            <a class="btn btn-primary text-nowrap btn-block {{ $problema->disponible ? '' : 'disabled' }}"
                                @if (isset($id_curso)) href="{{ route('problemas.resolver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}" @endif
                                role="button">{{ $problema->disponible ? 'Resolver Problema' : 'Problema No Disponible' }}</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary btn-sm btn-block"
                                href="{{ route('problemas.pdf_enunciado', ['id_problema' => $problema->id]) }}"
                                target="_blank" role="button">Descargar PDF del Enunciado</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block {{ isset($problema->body_editorial) ? '' : 'disabled' }}"
                                href="{{ route('problemas.ver_editorial', ['codigo' => $problema->codigo, 'id_curso' => $id_curso]) }}"
                                role="button">{{ isset($problema->body_editorial) ? 'Ver Pistas' : 'Pistas No Disponible' }}</a>
                        </div>
                        @can('ver informe del problema')
                            <div class="row px-5 mt-2 mb-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                    href="{{ route('informe.problema', ['id_curso' => $id_curso, 'id_problema' => $problema->id]) }}"
                                    role="button">Ver Informe del Problema</a>
                            </div>
                        @endcan
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                href="{{ route('envios.listado', ['id_problema' => $problema->id]) }}" role="button">Ver Mis
                                Envios</a>
                        </div>
                        <hr>
                        <h6 class="ms-3 mt-3"><strong>Información:</strong></h6>
                        <ul class="list-group mt-3">
                            <li class="list-group-item"><strong>Puntos:</strong>
                                {{ $problema->casos_de_prueba()->sum('puntos') }}</li>
                            <li class="list-group-item"><strong>Límite de Tiempo:</strong>
                                {{ $problema->tiempo_limite ? $problema->tiempo_limite . ' s' : 'No definido' }}</li>
                            <li class="list-group-item"><strong>Límite de Memoria:</strong>
                                {{ $problema->memoria_limite ? $problema->memoria_limite . ' KB' : 'No definido' }}</li>
                            <li class="list-group-item"><strong>Curso(s):</strong>
                                {{ implode(', ', $problema->cursos()->where('cursos.id', '=', $id_curso)->pluck('nombre')->toArray()) }}
                            </li>
                            <li class="list-group-item"><strong>Estado:</strong> <span
                                    class="badge {{ $problema->estado ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $problema->estado ? 'Resuelto' : 'No Resuelto' }}</span>
                            </li>
                            <li class="list-group-item"><strong>Categorías:</strong>
                                {{ implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray()) }}</li>
                            <li class="list-group-item"><strong>Lenguajes:</strong>
                                @foreach($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $item) 
                                <span class="badge text-bg-secondary">{{strtoupper($item)}}</span>
                                @endforeach
                            </li>
                            @if (isset($problema->fecha_inicio))
                                <li class="list-group-item"><strong>Fecha de Inicio:</strong> {{ $problema->fecha_inicio }}
                                </li>
                            @endif
                            @if (isset($problema->fecha_termino))
                                <li class="list-group-item"><strong>Fecha de Termino:</strong>
                                    {{ $problema->fecha_termino }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var table = document.querySelectorAll("#body_markdown table")
        if (table != null) {
            for(var i = 0; i<table.length; i++){
                var table_body = table[i].querySelector("tbody")
                table[i].classList.add("table")
                table[i].classList.add("table-bordered")
                table[i].classList.add("table-hover")
                table[i].classList.add("mt-3")
                table[i].style.width = "auto"
                table_body.classList.add("table-group-divider")
            }
            
        }
    </script>
@endpush
