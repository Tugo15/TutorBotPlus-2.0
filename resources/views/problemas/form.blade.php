<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
    <div>
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code text-primary me-2"></i>Información del Problema</h6>
        <p class="text-xs text-secondary mb-0">Configure los parámetros de ejecución, fechas, cursos y enunciado.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('nombre') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="nombre" placeholder="Ej. Sumar A y B"
                value="{{ isset($problema) ? old('nombre', $problema->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group has-danger">
            <label for="codigo"
                class="form-control-label @error('codigo') is-invalid @enderror">Código Único (Slug / Identificador)*</label>
            <input class="form-control" type="text" name="codigo" id="codigo" placeholder="Ej. suma-a-b o PROB-01"
                value="{{ isset($problema) ? old('codigo', $problema->codigo) : old('codigo') }}">
            <p class="text-xs text-secondary mt-1 mb-0"><small>Identificador único sin espacios ni tildes.</small></p>
            @error('codigo')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="dificultad" class="form-control-label font-weight-bold">Nivel de Dificultad</label>
            @php
                $difProb = isset($problema) ? old('dificultad', $problema->dificultad ?? 'Medio') : old('dificultad', 'Medio');
            @endphp
            <select class="form-select" id="dificultad" name="dificultad">
                <option value="Fácil" @if($difProb == 'Fácil') selected @endif>Fácil</option>
                <option value="Medio" @if($difProb == 'Medio') selected @endif>Medio</option>
                <option value="Difícil" @if($difProb == 'Difícil') selected @endif>Difícil</option>
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_inicio" class="form-control-label @error('fecha_inicio') is-invalid @enderror">Fecha de
                Inicio</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="set_fecha_inicio" name="set_fecha_inicio" value="1" @if(isset($problema->fecha_inicio) || old('set_fecha_inicio', false) == true) checked @endif>
                <label class="form-check-label" for="set_fecha_inicio">Establecer Fecha de Inicio</label>
            </div>
            <p class="opacity-25 fecha_inicio_class @if(!isset($problema->fecha_inicio) && old('set_fecha_inicio', false) == false) d-none @endif"><small>Indica la disponibilidad del problema, en caso de no ingresar estará disponible
                    de manera
                    inmediata.</small></p>
            <input class="form-control fecha_inicio_class @if(!isset($problema->fecha_inicio) && old('set_fecha_inicio', false) == false) d-none @endif" type="date" name="fecha_inicio" id="fecha_inicio" @if(!isset($problema->fecha_inicio) && old('set_fecha_inicio', false) == false) disabled @endif value="{{isset($problema)? old('fecha_inicio', $problema->fecha_inicio) : old('fecha_inicio')}}">
            @error('fecha_inicio')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_termino" class="form-control-label">Fecha de Termino</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="set_fecha_termino" name="set_fecha_termino" value="1" @if(isset($problema->fecha_termino) || old('set_fecha_termino', false) == true) checked @endif>
                <label class="form-check-label" for="set_fecha_termino">Establecer Fecha de Termino</label>
            </div>
            <p class="opacity-25 fecha_termino_class @if(!isset($problema->fecha_termino) && old('set_fecha_termino', false) == false) d-none @endif"><small>Indica hasta que fecha estara disponible el problema, en caso de no ingresar
                    estara disponible hasta que el usuario desee editarlo, eliminarlo o ocultarlo.</small></p>
            <input class="form-control fecha_termino_class @if(!isset($problema->fecha_termino) && old('set_fecha_termino', false) == false) d-none @endif  @error('fecha_termino') is-invalid @enderror" type="date" name="fecha_termino"
                id="fecha_termino" @if(!isset($problema->fecha_termino) && old('set_fecha_termino', false) == false) disabled @endif value="{{isset($problema)? old('fecha_termino', $problema->fecha_termino) : old('fecha_termino')}}">
            @error('fecha_termino')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="memoria_limite" class="form-control-label">Memoria Límite</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control  @error('memoria_limite') is-invalid @enderror"
                    name="memoria_limite" id="memoria_limite" placeholder="Ej. 5000 (5MB)" min="0"
                    value="{{ isset($problema) ? old('memoria_limite', $problema->memoria_limite) : old('memoria_limite') }}">
                <span class="input-group-text" id="basic-addon1">KB</span>
            </div>
            @error('memoria_limite')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="tiempo_limite" class="form-control-label">Tiempo Límite</label>
            <div class="input-group mb-3">

                <input type="number" class="form-control @error('tiempo_limite') is-invalid @enderror"
                    name="tiempo_limite" id="tiempo_limite" placeholder="Ej. 5" min="0" step=".1"
                    value="{{ isset($problema) ? old('tiempo_limite', $problema->tiempo_limite) : old('tiempo_limite') }}">
                <span class="input-group-text" id="basic-addon1">Segundos</span>
            </div>
            @error('tiempo_limite')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="form-check my-3">
    <input class="form-check-input" type="checkbox" value="{{ true }}" id="visible" name="visible"
        @if ((isset($problema) && $problema->visible == true) || old('visible')) checked @endif>
    <label class="form-check-label" for="visible">
        Mostrar el problema en el listado de problemas de los cursos.
    </label>
</div>
<label>Si no ingresa el tiempo y la memoria límite, el juez virtual no evaluara estos dos parametros.</label>
<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom mt-4">
    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-file-alt text-primary me-2"></i>Enunciado del Problema</h6>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="body_problema">Enunciado del Problema*</label>
            <input type="hidden" id="body_problema" name="body_problema"
                value="{{ isset($problema) ? old('body_problema', $problema->body_problema) : old('body_problema') }}">
            <div class="flex flex-col space-y-2">
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>
        </div>
        @error('body_problema')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold"><i class="fa fa-graduation-cap text-warning me-2"></i>Cursos Asignados*</h6>
                        <p class="text-xs text-secondary mb-0">Seleccione los cursos donde estará disponible este problema:</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0 me-1" onclick="toggleSelectionCards('prob-cursos-container', true)">Seleccionar Todos</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="toggleSelectionCards('prob-cursos-container', false)">Desmarcar Todos</button>
                    </div>
                </div>
                <div class="row g-2" id="prob-cursos-container">
                    @foreach ($cursos as $curso)
                        @php
                            $isChecked = (isset($problema) && $problema->cursos()->get()->contains($curso->id)) || (old('cursos') && in_array($curso->id, old('cursos'))) || (isset($id_curso_preseleccionado) && $id_curso_preseleccionado == $curso->id);
                        @endphp
                        <div class="col-md-4 col-sm-6">
                            <label class="selection-card w-100 mb-0 @if($isChecked) checked @endif" for="prob_curso_{{ $curso->id }}">
                                <input type="checkbox" id="prob_curso_{{ $curso->id }}" name="cursos[]" value="{{ $curso->id }}" @if($isChecked) checked @endif onchange="this.closest('.selection-card').classList.toggle('checked', this.checked)">
                                <div class="selection-card-content">
                                    <div class="selection-card-title">{{ $curso->nombre }}</div>
                                    <div class="selection-card-subtitle">Código: <span class="badge bg-primary text-xxs px-2 py-1">{{ $curso->codigo }}</span></div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('cursos')
                    <p class="text-danger text-xs pt-2 mb-0"> {{ $message }} </p>
                @enderror
            </div>
        </div>

        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold"><i class="fa fa-tags text-primary me-2"></i>Categorías*</h6>
                        <p class="text-xs text-secondary mb-0">Seleccione las categorías a las que pertenece este problema:</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0 me-1" onclick="toggleSelectionCards('prob-categorias-container', true)">Seleccionar Todos</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="toggleSelectionCards('prob-categorias-container', false)">Desmarcar Todos</button>
                    </div>
                </div>
                <div class="row g-2" id="prob-categorias-container">
                    @foreach ($categorias as $categoria)
                        @php
                            $isChecked = (isset($problema) && $problema->categorias()->get()->contains($categoria->id)) || (old('categorias') && in_array($categoria->id, old('categorias')));
                        @endphp
                        <div class="col-md-3 col-sm-6">
                            <label class="selection-card w-100 mb-0 @if($isChecked) checked @endif" for="prob_cat_{{ $categoria->id }}">
                                <input type="checkbox" id="prob_cat_{{ $categoria->id }}" name="categorias[]" value="{{ $categoria->id }}" @if($isChecked) checked @endif onchange="this.closest('.selection-card').classList.toggle('checked', this.checked)">
                                <div class="selection-card-content">
                                    <div class="selection-card-title">{{ $categoria->nombre }}</div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('categorias')
                    <p class="text-danger text-xs pt-2 mb-0"> {{ $message }} </p>
                @enderror
            </div>
        </div>

        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" value="1" id="sql" name="sql"
                        @if ((isset($problema) && $problema->sql == true) || old('sql')) checked @endif>
                    <label class="form-check-label font-weight-bold text-sm" for="sql">
                        Es un Problema de Consultas SQL
                    </label>
                </div>

                <div id="div_lenguajes">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 text-sm font-weight-bold"><i class="fa fa-code text-danger me-2"></i>Lenguajes de Programación Permitidos*</h6>
                            <p class="text-xs text-secondary mb-0">Marque los lenguajes en los cuales los estudiantes podrán resolver el problema:</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-xs btn-outline-secondary mb-0 me-1" onclick="toggleSelectionCards('prob-lenguajes-container', true)">Seleccionar Todos</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="toggleSelectionCards('prob-lenguajes-container', false)">Desmarcar Todos</button>
                        </div>
                    </div>
                    <div class="row g-2" id="prob-lenguajes-container">
                        @foreach ($lenguajes as $lenguaje)
                            @php
                                $isChecked = (isset($problema) && $problema->lenguajes()->get()->contains($lenguaje->id)) || (old('lenguajes') && in_array($lenguaje->id, old('lenguajes')));
                                $isDisabled = (isset($problema) && $problema->sql) || old('sql');
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <label class="selection-card w-100 mb-0 @if($isChecked) checked @endif @if($isDisabled) disabled @endif" for="prob_leng_{{ $lenguaje->id }}">
                                    <input class="lenguaje-checkbox" type="checkbox" id="prob_leng_{{ $lenguaje->id }}" name="lenguajes[]" value="{{ $lenguaje->id }}" @if($isChecked) checked @endif @if($isDisabled) disabled @endif onchange="this.closest('.selection-card').classList.toggle('checked', this.checked)">
                                    <div class="selection-card-content">
                                        <div class="selection-card-title">{{ $lenguaje->nombre }}</div>
                                        <div class="selection-card-subtitle"><span class="badge bg-info text-xxs px-2 py-1">{{ $lenguaje->abreviatura }}</span></div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('lenguajes')
                        <p class="text-danger text-xs pt-2 mb-0"> {{ $message }} </p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
<div id="sql_file" class="card border shadow-xs mb-3 p-3 @if ((isset($problema) && $problema->sql == false) || (!isset($problema) && old('sql', false) == false)) ) d-none @endif">
    <h6 class="mb-1 text-sm font-weight-bold text-dark"><i class="fa fa-database text-warning me-2"></i>Base de Datos SQLite (Solo para problemas de SQL)</h6>
    <p class="text-xs text-secondary mb-3">Suba el archivo de la base de datos en .sqlite comprimido en .zip que se utilizará para realizar la evaluación de consultas SQL.</p>
    <div class="mb-3">
        <input class="form-control form-control-sm" id="archivos_adicionales" name="archivos_adicionales"
            type="file">
        @error('archivos_adicionales')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
        <span class="text-xxs text-muted">Formato soportado: .zip</span>
    </div>
</div>
<hr>
@include('problemas.form_llm')
