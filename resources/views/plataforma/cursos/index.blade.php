@extends('layout_plataforma.app', ['title' => '¡Hola, ' . auth()->user()->firstname . ' ' . auth()->user()->lastname . '! 👋', 'title_html' => 'Cursos', 'breadcrumbs'=>[["nombre"=>"Cursos"]]])
@section('content')
    @include('components.alert')
    <div class="container-fluid px-4">
        <div class="card border-danger">
            <div class="card-body">
                <h5 class="ms-5"><strong>Vista de Cursos</strong></h5>
                <hr>
                <div class="d-flex justify-content-start align-items-start px-5 mb-4">
                    <form action="{{ route('cursos.listado') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Buscar Curso" id="buscar_curso"
                                name="buscar_curso" value="{{isset($busqueda)? $busqueda : ""}}">
                            <button class="btn btn-outline-secondary" type="submit">Buscar</button>

                        </div>
                    </form>
                </div>
                @forelse ($cursos as $curso)
                    <a href="{{ route('problemas.listado', ['id' => $curso->id]) }}" style="text-decoration: none;">
                        <div class="card rounded mb-3 ms-5 me-4">
                            <div class="row g-0">
                                <div class="col-sm-3">
                                    <div class="curso_image rounded"></div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $curso->nombre }}</h5>
                                        <p class="card-text mt-3">{{ $curso->descripcion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <h5 class="ms-5">No se ha encontrado el curso</h5>
                @endforelse
                <div class="d-flex flex-column justify-content-center mt-5 mx-5">
                    {{ $cursos->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
