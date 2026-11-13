@extends('layout_plataforma.app', ['title_html' => 'Mi Perfil', 'title' => 'Mi Perfil', 'breadcrumbs' => [['nombre' => 'Mi Perfil']]])
@section('content')
    <div class="container-fluid py-3 px-5">
        <div id="alert">
        @include('components.alert')
        </div>
        <div class="card border-danger">
            <div class="card-header">
                <strong>Información Personal</strong>
            </div>
            <div class="card-body px-5">
                <p class="text-sm text-danger">* Obligatorio</p>
                <form action="{{ route('perfil.update') }}" method="POST" id="editForm"
                    onsubmit="event.preventDefault();submitFormEditar()">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label for="username" class="form-label">Nombre de Usuario*</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                    id="username" name="username" maxlength="255" required autocomplete="username" value="{{ old('username', $info->username) }}">
                            </div>
                            @error('username')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="email" class="form-label">Correo Electrónico*</label>
                            <div class="input-group mb-3">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Ej. test@tutorbot.com" id="email" name="email" maxlength="255" required autocomplete="email"
                                    value="{{ old('email', $info->email) }}">
                            </div>
                            @error('email')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="firstname" class="form-label">Nombre*</label>
                            <div class="input-group mb-3">
                                <input type="text" id="firstname" name="firstname" maxlength="100" required autocomplete="given-name"
                                    class="form-control @error('firstname') is-invalid @enderror"
                                    value="{{ old('firstname', $info->firstname) }}">
                            </div>
                            @error('firstname')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="lastname" class="form-label">Apellido*</label>
                            <div class="input-group mb-3">
                                <input type="text" id="lastname" name="lastname" maxlength="100" required autocomplete="family-name"
                                    class="form-control @error('lastname') is-invalid @enderror"
                                    value="{{ old('lastname', $info->lastname) }}">
                            </div>
                            @error('lastname')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                    </div>


                    <label for="rut" class="form-label">Rut*</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control @error('rut') is-invalid @enderror" id="rut"
                            name="rut" value="{{ old('rut', $info->rut) }}" maxlength="10" required autocomplete="off" oninput="checkRut(this)">
                    </div>
                    @error('rut')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <hr>

                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password_actual') is-invalid @enderror"
                            id="password_actual" name="password_actual" autocomplete="current-password">
                        <button class="btn btn-outline-secondary mb-0" type="button" aria-label="Mostrar u ocultar contraseña actual"
                            onclick="const inp = document.getElementById('password_actual'); const icon = this.querySelector('i'); const isPwd = inp.type === 'password'; inp.type = isPwd ? 'text' : 'password'; icon.className = isPwd ? 'fa fa-eye-slash' : 'fa fa-eye';">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password_actual')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" autocomplete="new-password">
                        <button class="btn btn-outline-secondary mb-0" type="button" aria-label="Mostrar u ocultar nueva contraseña"
                            onclick="const inp = document.getElementById('password'); const icon = this.querySelector('i'); const isPwd = inp.type === 'password'; inp.type = isPwd ? 'text' : 'password'; icon.className = isPwd ? 'fa fa-eye-slash' : 'fa fa-eye';">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <label for="password_confirmation" class="form-label">Confirmar Nueva
                        Contraseña</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        <button class="btn btn-outline-secondary mb-0" type="button" aria-label="Mostrar u ocultar confirmación"
                            onclick="const inp = document.getElementById('password_confirmation'); const icon = this.querySelector('i'); const isPwd = inp.type === 'password'; inp.type = isPwd ? 'text' : 'password'; icon.className = isPwd ? 'fa fa-eye-slash' : 'fa fa-eye';">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-warning mt-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-shield me-2 text-warning"></i>Seguridad y Sesiones Activas</strong>
            </div>
            <div class="card-body px-3 px-md-5 py-4">
                <p class="text-sm text-secondary">
                    Si sospechas que tu cuenta está abierta en otro navegador o dispositivo no autorizado, puedes finalizar remotamente todas las demás sesiones activas.
                </p>
                <form action="{{ route('perfil.cerrar_otras_sesiones') }}" method="POST" onsubmit="return confirmarCerrarOtrasSesiones(event, this);">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fa fa-power-off me-1"></i> Cerrar todas mis otras sesiones activas
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/rutFormatting.js') }}"></script>
    <script>
        var profileFormSubmitted = false;
        function confirmarCerrarOtrasSesiones(e, form) {
            if (e) e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cerrar otras sesiones activas?',
                    text: 'Se cerrará la sesión en todos los demás navegadores o dispositivos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, cerrar sesiones',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else if (confirm('¿Estás seguro de que deseas cerrar la sesión en todos los demás dispositivos?')) {
                form.submit();
            }
            return false;
        }

        function submitFormEditar() {
            if (profileFormSubmitted) return;
            Swal.fire({
                title: "¿Estás seguro que quieres modificar tu perfil? Verifica que los datos estén correctos.",
                icon: "warning",
                showDenyButton: true,
                confirmButtonText: "Sí",
                denyButtonText: `No`
            }).then((result) => {
                if (result.isConfirmed) {
                    profileFormSubmitted = true;
                    document.getElementById('editForm').submit();
                }
            });
        }
    </script>
@endpush
