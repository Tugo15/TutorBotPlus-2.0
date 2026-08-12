<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-tags me-2"></i>Información de la Categoría</h6>
        <p class="text-xs text-secondary mb-0">Ingrese el nombre de la categoría para agrupar problemas.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-12">
        <div class="form-group has-danger mb-3">
            <label for="nombre" class="form-control-label font-weight-bold text-sm @error('nombre') is-invalid @enderror">Nombre de la Categoría*</label>
            <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Ej. Estructuras de Datos, Algoritmos Básicos, etc."
                value="{{ isset($categoria) ? old('nombre', $categoria->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>