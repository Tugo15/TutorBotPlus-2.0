<p class="text-uppercase text-sm">Información del Lenguaje de Programación</p>
<p class="text-sm text-danger">* Obligatorio</p>
<p>Antes de crear el lenguaje, debe verificar de que el Juez Virtual tenga
    soporte para el lenguaje de programación que desea crear,
    ya sea mediante la documentación o accediendo a la API del Juez Virtual.</p>

<div class="row">
    <div class="form-group has-danger">
        <label for="nombre" class="form-control-label @error('nombre') is-invalid @enderror">Nombre*</label>
        <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Ej. JavaScript"
            value="{{ isset($lenguaje) ? old('nombre', $lenguaje->nombre) : old('nombre') }}">
        @error('nombre')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group has-danger">
        <label for="abreviatura"
            class="form-control-label @error('abreviatura') is-invalid @enderror">Abreviatura*</label>
        <input class="form-control" type="text" id="abreviatura" name="abreviatura"
            placeholder="Ej. JS (abreviatura de JavaScript)"
            value="{{ isset($lenguaje) ? old('abreviatura', $lenguaje->abreviatura) : old('abreviatura') }}">
        @error('abreviatura')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group has-danger">
        <label for="example-text-input"
            class="form-control-label @error('extension') is-invalid @enderror">Extensión*</label>
        <input class="form-control" type="text" name="extension" placeholder="Ej. .js"
            value="{{ isset($lenguaje) ? old('extension', $lenguaje->extension) : old('extension') }}">
        @error('extension')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group has-danger">
        <label for="codigo" class="form-control-label @error('codigo') is-invalid @enderror">Código*</label>
        <label for="nombre">El código debe ser el id del lenguaje en el Juez Virtual, Ejemplo 91 es para Java:</label>
        <input class="form-control" type="text" name="codigo" placeholder="Ej. 63"
            value="{{ isset($lenguaje) ? old('codigo', $lenguaje->codigo) : old('codigo') }}">
        @error('codigo')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
