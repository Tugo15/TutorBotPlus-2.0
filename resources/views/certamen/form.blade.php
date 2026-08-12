<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-calendar-check me-2"></i>Información de la Evaluación</h6>
        <p class="text-xs text-secondary mb-0">Configure los parámetros, fechas y curso al que pertenece la evaluación.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-12">
        <div class="form-group has-danger mb-3">
            <label for="nombre" class="form-control-label font-weight-bold text-sm @error('nombre') is-invalid @enderror">Nombre de la Evaluación*</label>
            <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Ej. Certamen #1 - Algoritmos y Estructuras"
                value="{{ isset($certamen) ? old('nombre', $certamen->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="fecha_inicio" class="form-control-label font-weight-bold text-sm @error('fecha_inicio') is-invalid @enderror">Fecha de Inicio*</label>
            <input class="form-control" type="datetime-local" name="fecha_inicio" id="fecha_inicio" value="{{isset($certamen)? old('fecha_inicio', $certamen->fecha_inicio) : old('fecha_inicio')}}">
            @error('fecha_inicio')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="fecha_termino" class="form-control-label font-weight-bold text-sm @error('fecha_termino') is-invalid @enderror">Fecha de Término*</label>
            <input class="form-control" type="datetime-local" name="fecha_termino" id="fecha_termino" value="{{isset($certamen)? old('fecha_termino', $certamen->fecha_termino) : old('fecha_termino')}}">
            @error('fecha_termino')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 mb-3">
        <div class="form-group">
            <label for="descripcion" class="form-control-label font-weight-bold text-sm">Descripción del Certamen*</label>
            <input type="hidden" id="descripcion" name="descripcion"
                value="{{ isset($certamen) ? old('descripcion', $certamen->descripcion) : old('descripcion') }}">
            <div class="flex flex-col space-y-2">
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>
        </div>
        @error('descripcion')
            <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="form-group">
            <label for="curso" class="form-control-label font-weight-bold text-sm">Curso Perteneciente*</label>
            <select class="form-select" id="curso" name="curso">
                <option value="">Selecciona un curso</option>
                @foreach ($cursos as $curso)
                    <option value="{{ $curso->id }}" @if (
                        (isset($certamen) &&
                            $certamen->curso->id == $curso->id) ||
                            (old('curso') == $curso->id)) selected @endif>
                        {{ $curso->nombre }} ({{ $curso->codigo }})</option>
                @endforeach
            </select>
        </div>
        @error('curso')
            <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <div class="form-group has-danger">
            <label for="penalizacion_error" class="form-control-label font-weight-bold text-sm @error('penalizacion_error') is-invalid @enderror">Penalización por Error</label>
            <input class="form-control" type="number" id="penalizacion_error" name="penalizacion_error" placeholder="Ej. 0.5" min="0" step="0.01"
                value="{{ isset($certamen) ? old('penalizacion_error', $certamen->penalizacion_error) : old('penalizacion_error', 0) }}">
            @error('penalizacion_error')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="form-group has-danger">
            <label for="cantidad_penalizacion" class="form-control-label font-weight-bold text-sm @error('cantidad_penalizacion') is-invalid @enderror">Máx. Penalizaciones</label>
            <input class="form-control" type="number" id="cantidad_penalizacion" name="cantidad_penalizacion" placeholder="Ej. 3" min="0"
                value="{{ isset($certamen) ? old('cantidad_penalizacion', $certamen->cantidad_penalizacion) : old('cantidad_penalizacion', 0) }}">
            @error('cantidad_penalizacion')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>