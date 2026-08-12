@php
$old_cursos = old('cursos')? old('cursos') : [];
$old_roles = old('roles')? old('roles') : [];
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-user me-2"></i>Información Personal y Accesos</h6>
        <p class="text-xs text-secondary mb-0">Ingrese los datos personales del usuario, asigne sus roles y cursos.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input" class="form-control-label @error('username') is-invalid @enderror">Nombre de
                Usuario*</label>
            <input class="form-control" type="text" name="username" placeholder="Ej. jmacias"
                value="{{ isset($user) ? old('username', $user->username) : old('username') }}">
            @error('username')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('email') is-invalid @enderror">Correo*</label>
            <input class="form-control" type="email" name="email" placeholder="Ej. estudiante@tutorbot.com"
                value="{{ isset($user) ? old('email', $user->email) : old('email') }}">
            @error('email')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="example-text-input"
                class="form-control-label @error('firstname') is-invalid @enderror">Nombre*</label>
            <input class="form-control" type="text" name="firstname" placeholder="Ej. Armando"
                value="{{ isset($user) ? old('firstname', $user->firstname) : old('firstname') }}">
            @error('firstname')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger">
            <label for="lastname" class="form-control-label @error('lastname') is-invalid @enderror">Apellido*</label>
            <input class="form-control" type="text" name="lastname" placeholder="Ej. Casas"
                value="{{ isset($user) ? old('lastname', $user->lastname) : old('lastname') }}">
            @error('lastname')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col">
        <div class="form-group has-danger">
            <label for="rut" class="form-control-label @error('rut') is-invalid @enderror">Rut*</label>
            <input class="form-control" type="text" name="rut" placeholder="Ej. 12345678-9"
                value="{{ isset($user) ? old('rut', $user->rut) : old('rut') }}" maxlength="10" oninput="checkRut(this)">
            @error('rut')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <p>La contraseña para ingresar a la plataforma sera el RUT sin dígito verificador</p>
</div>
<div class="row">
    <div class="col">
        <label class="form-control-label" for="roles">Roles*</label>
        <div class="form-group has-danger">
            @foreach ($roles as $role)  
                <div class="form-check form-check-inline" id="roles">
                    <input class="form-check-input" type="checkbox" id="rol_{{ $role->id }}"
                        name="roles[]" value="{{ $role->name }}" @if((isset($user) && $user->hasRole($role->name))||(!isset($user) && in_array($role->name,$old_roles))) checked @endif @if(isset($user) && $role->name=="administrador" && $user->id == auth()->user()->id) disabled @endif>
                    <label class="form-check-label" for="rol_{{ $role->id }}">{{ ucFirst($role->name) }}</label>
                </div>
            @endforeach
        </div>
        @error('roles')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border shadow-xs">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold"><i class="fa fa-graduation-cap text-warning me-2"></i>Asignar Cursos</h6>
                        <p class="text-xs text-secondary mb-0">Haga clic en los cursos para inscribir al usuario:</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0 me-1" onclick="toggleSelectionCards('user-cursos-container', true)">Seleccionar Todos</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0" onclick="toggleSelectionCards('user-cursos-container', false)">Desmarcar Todos</button>
                    </div>
                </div>
                <div class="row g-2" id="user-cursos-container">
                    @foreach($cursos as $curso)
                        @php
                            $isChecked = (isset($user) && $user->cursos()->get()->contains($curso)) || (!isset($user) && in_array($curso->id, $old_cursos));
                        @endphp
                        <div class="col-md-4 col-sm-6">
                            <label class="selection-card w-100 mb-0 @if($isChecked) checked @endif" for="curso_{{ $curso->id }}">
                                <input type="checkbox" id="curso_{{ $curso->id }}" name="cursos[]" value="{{ $curso->id }}" @if($isChecked) checked @endif onchange="this.closest('.selection-card').classList.toggle('checked', this.checked)">
                                <div class="selection-card-content">
                                    <div class="selection-card-title">{{ $curso->nombre }}</div>
                                    <div class="selection-card-subtitle">Código: <span class="badge bg-gradient-primary text-xxs px-2 py-1">{{ $curso->codigo }}</span></div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('cursos')
                    <p class="text-danger text-xs pt-2 mb-0"> {{ $message }} </p>
                @enderror
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSelectionCards(containerId, state) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var checkboxes = container.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(cb) {
            cb.checked = state;
            var card = cb.closest('.selection-card');
            if (card) card.classList.toggle('checked', state);
        });
    }
</script>