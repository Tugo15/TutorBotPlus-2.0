<p class="text-uppercase text-sm">Información del Rol</p>
<p class="text-sm text-danger">* Obligatorio</p>
<div class="row">
    <div class="col">
        <div class="form-group has-danger">
            <label for="example-text-input" class="form-control-label @error('name') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="name" placeholder="Ej. Administrador"
                value="{{ isset($rol) ? old('name', $rol->name) : old('name') }}">
            @error('name')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <label class="form-control-label" for="permisos">Permisos* (Escoger al menos uno)</label>
        <div class="form-group has-danger">
            @foreach ($permisos as $permiso)  
                <div class="form-check form-check-inline" id="permisos">
                    <input class="form-check-input" type="checkbox" id="permiso_{{ $permiso->id }}"
                        name="permisos[]" value="{{ $permiso->name }}" @if(isset($rol) && $rol->hasPermissionTo($permiso->name)) checked @endif>
                    <label class="form-check-label" for="permiso_{{ $permiso->id }}">{{ ucFirst($permiso->name) }}</label>
                </div>
            @endforeach
        </div>
        @error('permisos')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>