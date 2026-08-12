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
@php
    $groupedPermisos = [];
    foreach ($permisos as $permiso) {
        $name = strtolower($permiso->name);
        $group = 'Otros';
        if (str_contains($name, 'usuario')) {
            $group = 'Usuarios';
        } elseif (str_contains($name, 'rol')) {
            $group = 'Roles';
        } elseif (str_contains($name, 'curso')) {
            $group = 'Cursos';
        } elseif (str_contains($name, 'problema') && !str_contains($name, 'categoría')) {
            $group = 'Problemas';
        } elseif (str_contains($name, 'certamen') || str_contains($name, 'evaluacion')) {
            $group = 'Evaluaciones';
        } elseif (str_contains($name, 'categoría')) {
            $group = 'Categorías';
        } elseif (str_contains($name, 'lenguaje')) {
            $group = 'Lenguajes de Programación';
        }
        $groupedPermisos[$group][] = $permiso;
    }
@endphp

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <label class="form-control-label font-weight-bold text-sm">Permisos del Rol*</label>
                <p class="text-xs text-secondary mb-0">Seleccione los permisos agrupados por módulo:</p>
            </div>
            <div>
                <button type="button" class="btn btn-xs btn-outline-secondary mb-0 me-1" onclick="toggleSelectionCards('roles-permisos-container', true)">Seleccionar Todos</button>
                <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="toggleSelectionCards('roles-permisos-container', false)">Desmarcar Todos</button>
            </div>
        </div>

        <div id="roles-permisos-container">
            @foreach ($groupedPermisos as $groupName => $groupItems)
                <div class="card border shadow-xs mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-xs font-weight-bold text-uppercase text-primary">
                                <i class="fa fa-folder-open me-1"></i> Módulo {{ $groupName }}
                            </h6>
                            <button type="button" class="btn btn-link text-xs text-secondary mb-0 p-0" onclick="toggleGroupCheckboxes(this)">Alternar Grupo</button>
                        </div>
                        <div class="row g-2">
                            @foreach ($groupItems as $permiso)
                                @php
                                    $isChecked = isset($rol) && $rol->hasPermissionTo($permiso->name);
                                @endphp
                                <div class="col-md-3 col-sm-6">
                                    <label class="selection-card w-100 mb-0 @if($isChecked) checked @endif" for="permiso_{{ $permiso->id }}">
                                        <input type="checkbox" id="permiso_{{ $permiso->id }}" name="permisos[]" value="{{ $permiso->name }}" @if($isChecked) checked @endif onchange="this.closest('.selection-card').classList.toggle('checked', this.checked)">
                                        <div class="selection-card-content">
                                            <div class="selection-card-title text-capitalize">{{ $permiso->name }}</div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @error('permisos')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>

<script>
    function toggleGroupCheckboxes(btn) {
        var cardBody = btn.closest('.card-body');
        if (!cardBody) return;
        var checkboxes = cardBody.querySelectorAll('input[type="checkbox"]');
        var anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(function(cb) {
            cb.checked = anyUnchecked;
            var card = cb.closest('.selection-card');
            if (card) card.classList.toggle('checked', anyUnchecked);
        });
    }
</script>