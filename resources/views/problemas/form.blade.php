<p class="text-uppercase text-sm">Información del Problema</p>
<p class="text-sm text-danger">* Obligatorio</p>
<div class="row">
    <div class="col-md-6">
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
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('codigo') is-invalid @enderror">Código*</label>
            <input class="form-control" type="text" name="codigo" placeholder="Ej. suma-a-b"
                value="{{ isset($problema) ? old('codigo', $problema->codigo) : old('codigo') }}">
            @error('codigo')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
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
<hr>
<p class="text-uppercase text-sm mt-2">Enunciado</p>
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
<div class="row">
    <div class="form-group">
        <label for="cursos">Cursos*</label>
        <label for="cursos">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="cursos" name="cursos[]">
            @foreach ($cursos as $curso)
                <option value="{{ $curso->id }}" @if (
                    (isset($problema) &&
                        $problema->cursos()->get()->contains($curso->id)) ||
                        (old('cursos') && in_array($curso->id, old('cursos')))) selected @endif>
                    {{ $curso->nombre }}</option>
            @endforeach
        </select>
    </div>
    @error('cursos')
        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
    @enderror
</div>

<div class="row">
    <div class="form-group">
        <label for="categorias">Categorías*</label>
        <label for="categorias">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="categorias" name="categorias[]">
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @if (
                    (isset($problema) &&
                        $problema->categorias()->get()->contains($categoria->id)) ||
                        (old('categorias') && in_array($categoria->id, old('categorias')))) selected @endif>
                    {{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-check my-3">
    <input class="form-check-input" type="checkbox" value="{{ true }}" id="sql" name="sql"
        @if ((isset($problema) && $problema->sql == true) || old('sql')) checked @endif>
    <label class="form-check-label" for="sql">
        Problema de SQL
    </label>
</div>
<div class="row" id="div_lenguajes">
    <div class="form-group">
        <label for="lenguajes">Lenguajes de Programación*</label>
        <label for="lenguajes">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="lenguajes" name="lenguajes[]"
            @if ((isset($problema) && $problema->sql) || old('sql')) disabled @endif>
            @foreach ($lenguajes as $lenguaje)
                <option value="{{ $lenguaje->id }}" @if (
                    (isset($problema) &&
                        $problema->lenguajes()->get()->contains($lenguaje->id)) ||
                        (old('lenguajes') && in_array($lenguaje->id, old('lenguajes')))) selected @endif>
                    {{ $lenguaje->nombre }}</option>
            @endforeach
        </select>
    </div>
    @error('lenguajes')
        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
    @enderror
</div>
<div id="sql_file" class="@if ((isset($problema) && $problema->sql == false) || (!isset($problema) && old('sql', false) == false)) ) d-none @endif">
    <p class="text-uppercase text-sm">Base de Datos (Solo para problemas de SQL)</p>
    <p class="text-sm">En caso de que sea un problema de SQL, suba el archivo de la base de datos en .sqlite comprimido
        en .zip
        que se utilizara para realizar las consultas.</p>
    <div class="mb-3">
        <input class="form-control form-control-sm" id="archivos_adicionales" name="archivos_adicionales"
            type="file">
        @error('archivos_adicionales')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
        <label for="archivos_adicionales">Formato: .zip</label>
    </div>
</div>
<hr>
@include('problemas.form_llm')
