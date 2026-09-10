<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-code me-2 text-warning"></i>Información del Lenguaje de Programación</h6>
        <p class="text-xs text-secondary mb-0">Verifique en Judge0 el ID del lenguaje antes de crearlo o editarlo.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>

<div class="alert alert-info text-white text-xs mb-4" role="alert">
    <i class="fa fa-info-circle me-1"></i> Asegúrese de que el Juez Virtual (Judge0) tenga soporte para el lenguaje que desea registrar.
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="nombre" class="form-control-label font-weight-bold text-sm @error('nombre') is-invalid @enderror">Nombre del Lenguaje*</label>
            <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Ej. Python 3.8.1"
                value="{{ isset($lenguaje) ? old('nombre', $lenguaje->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="abreviatura" class="form-control-label font-weight-bold text-sm @error('abreviatura') is-invalid @enderror">Abreviatura*</label>
            <input class="form-control" type="text" id="abreviatura" name="abreviatura" placeholder="Ej. PY, C++, JS"
                value="{{ isset($lenguaje) ? old('abreviatura', $lenguaje->abreviatura) : old('abreviatura') }}">
            @error('abreviatura')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="extension" class="form-control-label font-weight-bold text-sm @error('extension') is-invalid @enderror">Extensión de Archivo*</label>
            <input class="form-control" type="text" id="extension" name="extension" placeholder="Ej. .py, .cpp, .js"
                value="{{ isset($lenguaje) ? old('extension', $lenguaje->extension) : old('extension') }}">
            @error('extension')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="codigo" class="form-control-label font-weight-bold text-sm @error('codigo') is-invalid @enderror">ID Judge0 (Código)*</label>
            <input class="form-control" type="text" id="codigo" name="codigo" placeholder="Ej. 71 para Python, 91 para Java"
                value="{{ isset($lenguaje) ? old('codigo', $lenguaje->codigo) : old('codigo') }}">
            <p class="text-xs text-secondary mt-1 mb-0"><small>Es el ID numérico del lenguaje en la API del Juez Virtual.</small></p>
            @error('codigo')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
