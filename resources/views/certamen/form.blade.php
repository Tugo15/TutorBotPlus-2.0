<p class="text-uppercase text-sm">Información de la Evaluación</p>
<p class="text-sm text-danger">* Obligatorio</p>
<div class="row">
    <div class="col-12">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('nombre') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="nombre" placeholder="Ej. Test de laboratorio #1"
                value="{{ isset($certamen) ? old('nombre', $certamen->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_inicio" class="form-control-label @error('fecha_inicio') is-invalid @enderror">Fecha de
                Inicio*</label>
            <input class="form-control" type="datetime-local" name="fecha_inicio" id="fecha_inicio" value="{{isset($certamen)? old('fecha_inicio', $certamen->fecha_inicio) : old('fecha_inicio')}}">
            @error('fecha_inicio')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="fecha_termino" class="form-control-label">Fecha de Termino*</label>
            <input class="form-control @error('fecha_termino') is-invalid @enderror" type="datetime-local" name="fecha_termino"
                id="fecha_termino" value="{{isset($certamen)? old('fecha_termino', $certamen->fecha_termino) : old('fecha_termino')}}">
            @error('fecha_termino')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="descripcion">Descripción*</label>
            <input type="hidden" id="descripcion" name="descripcion"
                value="{{ isset($certamen) ? old('descripcion', $certamen->descripcion) : old('descripcion') }}">
            <div class="flex flex-col space-y-2">
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>
        </div>
        @error('descripcion')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="form-group">
        <label for="cursos">Curso*</label>
        <select class="form-control" id="curso" name="curso">
            <option>Selecciona un curso</option>
            @foreach ($cursos as $curso)
                <option value="{{ $curso->id }}" @if (
                    (isset($certamen) &&
                        $certamen->curso->id == $curso->id) ||
                        (old('curso') == $curso->id)) selected @endif>
                    {{ $curso->nombre }}</option>
            @endforeach
        </select>
    </div>
    @error('curso')
        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
    @enderror
</div>
<div class="row">
    <div class="form-group has-danger">
        <label for="example-text-input"
            class="form-control-label @error('penalizacion_error') is-invalid @enderror">Penalización por Error</label>
        <input class="form-control" type="number" name="penalizacion_error" placeholder="Ejemplo: 0.5 por error ocurrido" min="0" step="0.01"
            value="{{ isset($certamen) ? old('penalizacion_error', $certamen->penalizacion_error) : old('penalizacion_error', 0) }}">
        @error('penalizacion_error')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
    <div class="form-group has-danger">
        <label for="example-text-input"
            class="form-control-label @error('cantidad_penalizacion') is-invalid @enderror">Cantidad Máxima de Penalización</label>
        <input class="form-control" type="number" name="cantidad_penalizacion" placeholder="Ejemplo: solo 3 penalizaciones por error por problema" min="0"
            value="{{ isset($certamen) ? old('cantidad_penalizacion', $certamen->cantidad_penalizacion) : old('cantidad_penalizacion', 0) }}">
        @error('cantidad_penalizacion')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>