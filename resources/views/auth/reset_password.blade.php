@extends('layouts.app')

@section('content')
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <div class="d-flex justify-content-center">
                                        <img class="position-relative align-self-center mb-3"
                                        src="{{ asset('img/ico_tutorbot.png') }}" alt="" style="width:150px" id="mobileIcon">
                                    </div>
                                    <h4 class="font-weight-bolder">Restaurar Contraseña</h4>
                                    <p class="mb-0">Restauración de tu contraseña</p>
                                </div>
                                <div class="card-body">
                                    @if(!empty($tokenInvalido))
                                        <div class="alert alert-warning text-white text-xs mb-3" role="alert">
                                            <i class="fa fa-exclamation-triangle me-1"></i> Este enlace de recuperación no es válido o ya expiró. Solicita un nuevo correo de recuperación.
                                        </div>
                                    @endif
                                    <form role="form" method="POST" action="{{ route('password.update') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn.disabled) { return false; } btn.disabled = true;">
                                        @csrf
                                        <input type="hidden" name="token" value="{{$token}}">
                                        <div class="flex flex-col mb-3">
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Correo Electrónico" value="{{ old('email', request('email')) }}" aria-label="Correo Electrónico" autocomplete="email" maxlength="255" required>
                                            @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                        </div>
                                        <div class="flex flex-col mb-3">
                                            <div class="input-group">
                                                <input type="password" name="password" id="reset_password" class="form-control form-control-lg" placeholder="Nueva Contraseña (mín. 8 caracteres)" aria-label="Contraseña" autocomplete="new-password" maxlength="255" minlength="8" required>
                                                <button class="btn btn-outline-secondary mb-0 px-3" type="button" aria-label="Mostrar u ocultar nueva contraseña"
                                                    onclick="const inp = document.getElementById('reset_password'); const icon = this.querySelector('i'); const isPwd = inp.type === 'password'; inp.type = isPwd ? 'text' : 'password'; icon.className = isPwd ? 'fa fa-eye-slash' : 'fa fa-eye';">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            @error('password') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                        </div>
                                        <div class="flex flex-col mb-3">
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" id="reset_password_confirmation" class="form-control form-control-lg" placeholder="Repetir Contraseña" aria-label="Confirmar Contraseña" autocomplete="new-password" maxlength="255" minlength="8" required>
                                                <button class="btn btn-outline-secondary mb-0 px-3" type="button" aria-label="Mostrar u ocultar confirmación"
                                                    onclick="const inp = document.getElementById('reset_password_confirmation'); const icon = this.querySelector('i'); const isPwd = inp.type === 'password'; inp.type = isPwd ? 'text' : 'password'; icon.className = isPwd ? 'fa fa-eye-slash' : 'fa fa-eye';">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            @error('password_confirmation') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Restaurar Contraseña</button>
                                        </div>
                                    </form>
                                    <div class="card-footer text-center pt-3 px-1 pb-0">
                                        <p class="mb-0 text-sm mx-auto">
                                            <a href="{{ route('login') }}" class="text-primary font-weight-bold"><i class="fa fa-arrow-left me-1"></i> Volver al Inicio de Sesión</a>
                                        </p>
                                    </div>
                                </div>
                                <div id="alert">
                                    @include('components.alert')
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('{{asset('img/ucsc-image.jpg')}}');
                                        background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">Universidad Católica de la Santísima Concepción</h4>
                                <p class="text-white position-relative">Plataforma de Juez Virtual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
