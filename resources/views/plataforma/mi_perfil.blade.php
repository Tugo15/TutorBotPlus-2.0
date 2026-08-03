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
                                    id="username" name="username" value="{{ old('username', $info->username) }}">
                            </div>
                            @error('username')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="email" class="form-label">Correo Electrónico*</label>
                            <div class="input-group mb-3">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Ej. test@tutorbot.com" id="email" name="email"
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
                                <input type="text" id="firstname" name="firstname"
                                    class="form-control @error('firstname') is-invalid @enderror"
                                    value="{{ old('firstname', $info->firstname) }}">
                            </div>
                            @error('firstname')
                                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="firstname" class="form-label">Apellido*</label>
                            <div class="input-group mb-3">
                                <input type="text" id="lastname" name="lastname"
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
                            name="rut" value="{{ old('rut', $info->rut) }}" maxlength="10" oninput="checkRut(this)">
                    </div>
                    @error('rut')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <hr>

                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password_actual') is-invalid @enderror"
                            id="password_actual" name="password_actual">
                    </div>
                    @error('password_actual')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password">
                    </div>
                    @error('password')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror
                    <label for="password_confirmation" class="form-label">Confirmar Nueva
                        Contraseña</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" name="password_confirmation">

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
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/rutFormatting.js') }}"></script>
    <script>
        function submitFormEditar() {
            Swal.fire({
                title: "¿Estás seguro que quieres modificar tu perfil? Verifica que los datos estén correctos.",
                icon: "warning",
                showDenyButton: true,
                confirmButtonText: "Si",
                denyButtonText: `No`
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('editForm').submit();
                }
            });
        }
    </script>
@endpush
