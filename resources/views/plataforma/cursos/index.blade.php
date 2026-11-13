@extends('layout_plataforma.app', ['title' => '¡Hola, ' . auth()->user()->firstname . ' ' . auth()->user()->lastname . '! 👋', 'title_html' => 'Cursos', 'breadcrumbs'=>[["nombre"=>"Cursos"]]])
@section('content')
    @include('components.alert')
    <div class="container-fluid px-2 px-md-4">
        <div class="card border-danger">
            <div class="card-body px-3 px-md-4">
                <h5 class="ms-2 ms-md-5"><strong>Vista de Cursos</strong></h5>
                <hr>
                <div class="d-flex justify-content-start align-items-start px-2 px-md-5 mb-4">
                    <form action="{{ route('cursos.listado') }}" method="GET" class="w-100" style="max-width: 480px;" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn.disabled) { return false; } btn.disabled = true;">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Buscar Curso" id="buscar_curso"
                                name="buscar_curso" maxlength="255" value="{{ is_string($busqueda ?? null) ? $busqueda : '' }}">
                            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                            @if(!empty($busqueda))
                                <a href="{{ route('cursos.listado') }}" class="btn btn-outline-danger" title="Limpiar búsqueda">
                                    <i class="fa fa-times me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                @forelse ($cursos as $curso)
                    <a href="{{ route('problemas.listado', ['id' => $curso->id]) }}" style="text-decoration: none;">
                        <div class="card rounded mb-3 mx-1 ms-md-5 me-md-4">
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
                    <div class="ms-2 ms-md-5 py-3">
                        <h5 class="mb-2">No se ha encontrado el curso</h5>
                        @if(!empty($busqueda))
                            <p class="text-sm text-secondary mb-3">No hay cursos que coincidan con "<strong>{{ $busqueda }}</strong>".</p>
                            <a href="{{ route('cursos.listado') }}" class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-times-circle me-1"></i> Limpiar búsqueda y ver todos los cursos
                            </a>
                        @endif
                    </div>
                @endforelse
                <div class="d-flex flex-column justify-content-center mt-4 mt-md-5 mx-2 mx-md-5">
                    {{ $cursos->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
