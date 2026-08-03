<p class="text-uppercase text-sm">Información del Curso</p>
<p class="text-sm text-danger">* Obligatorio</p>
<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="nombre" class="form-control-label @error('nombre') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="nombre" placeholder="Ej. Taller de Python"
                value="{{ isset($curso) ? old('nombre', $curso->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="codigo" class="form-control-label @error('codigo') is-invalid @enderror">Código*</label>
            <input class="form-control" type="codigo" name="codigo" placeholder="Ej. IN1045C"
                value="{{ isset($curso) ? old('codigo', $curso->codigo) : old('codigo') }}">
            @error('codigo')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col">
        <div class="form-group has-danger">
            <label for="descripcion" class="form-control-label ">Descripción</label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                rows="3" placeholder="Introduzca una descripción del curso">{{ isset($curso) ? old('descripcion', $curso->descripcion) : old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
