<p class="text-uppercase text-sm">Información de la Categoría</p>
<p class="text-sm text-danger">* Obligatorio</p>
<div class="row">
    <div class="col">
        <div class="form-group has-danger">
            <label for="example-text-input" class="form-control-label @error('nombre') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="nombre" placeholder="Ej. Experto"
                value="{{ isset($categoria) ? old('nombre', $categoria->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>