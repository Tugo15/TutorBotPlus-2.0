<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-graduation-cap me-2 text-success"></i>Información del Curso</h6>
        <p class="text-xs text-secondary mb-0">Complete los datos básicos del curso a registrar en el sistema.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="nombre" class="form-control-label font-weight-bold text-sm @error('nombre') is-invalid @enderror">Nombre del Curso*</label>
            <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Ej. Taller de Programación Python"
                value="{{ isset($curso) ? old('nombre', $curso->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="codigo" class="form-control-label font-weight-bold text-sm @error('codigo') is-invalid @enderror">Código del Curso*</label>
            <input class="form-control" type="text" id="codigo" name="codigo" placeholder="Ej. INF-101"
                value="{{ isset($curso) ? old('codigo', $curso->codigo) : old('codigo') }}">
            @error('codigo')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-12">
        <div class="form-group has-danger mb-3">
            <label for="descripcion" class="form-control-label font-weight-bold text-sm">Descripción del Curso</label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                rows="3" placeholder="Introduzca una descripción detallada del curso">{{ isset($curso) ? old('descripcion', $curso->descripcion) : old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
