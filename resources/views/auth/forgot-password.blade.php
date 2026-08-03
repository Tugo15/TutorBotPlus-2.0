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
                                    <h4 class="font-weight-bolder">Recupera tu Contraseña</h4>
                                    <p class="mb-0">Ingresa tu correo electronico</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('password.email') }}">
                                        @csrf
                                        <div class="flex flex-col mb-3">
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Ej. estudiante@tutorbot.com" value="{{ old('email') }}" aria-label="Email">
                                            @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Recuperar Contraseña</button>
                                        </div>
                                        <div class="text-center">
                                            <a class="btn btn-lg btn-outline-primary btn-lg w-100 mt-2 mb-0" href="{{route('login')}}">Volver</a>
                                        </div>
                                    </form>
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
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">Universidad Católica de la Santisima Concepción</h4>
                                <p class="text-white position-relative">Plataforma de Juez Virtual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
