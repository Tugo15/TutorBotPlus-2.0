@php
$old_cursos = old('cursos')? old('cursos') : [];
$old_roles = old('roles')? old('roles') : [];
@endphp
<p class="text-uppercase text-sm">Información del usuario</p>
<p class="text-sm text-danger">* Obligatorio</p>
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

<div class="row">
    <div class="form-group">
        <label for="cursos">Curso</label>
        <label for="cursos">(Mantenga pulsado CTRL o CMD para seleccionar más de un curso)</label>
        <select multiple class="form-control" id="cursos" name="cursos[]">
            @foreach($cursos as $curso)
                <option value="{{$curso->id}}" @if((isset($user) && $user->cursos()->get()->contains($curso))||(!isset($user) && in_array($curso->id,$old_cursos))) selected @endif>{{$curso->nombre}}</option>
            @endforeach
        </select>
      </div>
      @error('cursos')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
      @enderror
</div>