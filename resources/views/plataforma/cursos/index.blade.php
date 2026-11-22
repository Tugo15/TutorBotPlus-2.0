@extends('layout_plataforma.app', ['title' => '¡Hola, ' . auth()->user()->firstname . ' ' . auth()->user()->lastname . '! 👋', 'title_html' => 'Cursos', 'breadcrumbs'=>[["nombre"=>"Cursos"]]])
@section('content')
    @include('components.alert')
    <div class="container-fluid px-3 px-md-4">
        <div class="card border-danger">
            <div class="card-body px-3 px-md-4 py-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0"><strong>Vista de Cursos</strong></h5>
                        <span class="badge text-bg-light border fw-semibold">{{ $cursos->total() }}</span>
                    </div>
                    <form action="{{ route('cursos.listado') }}" method="GET" class="w-100" style="max-width: 440px;" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn.disabled) { return false; } btn.disabled = true;">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar Curso" id="buscar_curso"
                                name="buscar_curso" maxlength="255" value="{{ is_string($busqueda ?? null) ? $busqueda : '' }}">
                            <button class="btn btn-outline-secondary d-inline-flex align-items-center" type="submit">
                                <i class="fa fa-search me-1"></i> Buscar
                            </button>
                            @if(!empty($busqueda))
                                <a href="{{ route('cursos.listado') }}" class="btn btn-outline-danger d-inline-flex align-items-center" title="Limpiar búsqueda">
                                    <i class="fa fa-times me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <hr class="mt-2 mb-4">

                <div class="row {{ $cursos->count() === 1 ? 'row-cols-1' : 'row-cols-1 row-cols-lg-2' }} g-3 g-md-4">
                    @forelse ($cursos as $curso)
                        <div class="col d-flex">
                            <a href="{{ route('problemas.listado', ['id' => $curso->id]) }}" class="curso-card-link">
                                <div class="card rounded overflow-hidden curso-card-horizontal">
                                    <div class="card-body py-3 px-3 px-sm-4 ps-4 d-flex flex-column justify-content-between h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="curso-card-emblem" aria-hidden="true">
                                                <i class="fa fa-laptop-code"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                @if(!empty($curso->codigo))
                                                    <span class="curso-card-code">{{ $curso->codigo }}</span>
                                                @endif
                                                <h5 class="card-title">{{ $curso->nombre }}</h5>
                                                <p class="card-text mb-0">{{ !empty(trim((string) $curso->descripcion)) ? $curso->descripcion : 'Este es un curso de programación' }}</p>
                                            </div>
                                            <i class="fa fa-chevron-right curso-card-arrow" aria-hidden="true"></i>
                                        </div>

                                        <div class="curso-card-progress">
                                            <div class="curso-progress-track" role="progressbar" aria-valuenow="{{ $curso->progreso_porcentaje ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="curso-progress-fill {{ ($curso->progreso_porcentaje ?? 0) >= 100 && ($curso->problemas_visibles_count ?? 0) > 0 ? 'is-complete' : '' }}"
                                                    style="width: {{ $curso->progreso_porcentaje ?? 0 }}%;"></div>
                                            </div>
                                            <div class="curso-card-meta">
                                                <div class="curso-card-meta-counts">
                                                    <span>
                                                        <i class="fa fa-check-circle {{ ($curso->problemas_resueltos_count ?? 0) > 0 ? 'text-success' : '' }}"></i>
                                                        {{ $curso->problemas_resueltos_count ?? 0 }}/{{ $curso->problemas_visibles_count ?? 0 }} resueltos
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-clipboard-list"></i>
                                                        {{ $curso->certamenes_count ?? 0 }} {{ ($curso->certamenes_count ?? 0) === 1 ? 'evaluación' : 'evaluaciones' }}
                                                    </span>
                                                </div>
                                                <span class="fw-semibold text-danger">{{ $curso->progreso_porcentaje ?? 0 }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 py-3">
                            <h5 class="mb-2">No se ha encontrado el curso</h5>
                            @if(!empty($busqueda))
                                <p class="text-sm text-secondary mb-3">No hay cursos que coincidan con "<strong>{{ $busqueda }}</strong>".</p>
                                <a href="{{ route('cursos.listado') }}" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-times-circle me-1"></i> Limpiar búsqueda y ver todos los cursos
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if($cursos->hasPages())
                    <div class="d-flex flex-column justify-content-center mt-4">
                        {{ $cursos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
