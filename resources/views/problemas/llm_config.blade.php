@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Configurar LLM de un problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Problema '.$problema->codigo.' - Configurar Large Language Model'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('problemas.configurar_llm', ['id'=>$problema->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('problemas.form_llm')
                    <input type="submit" class="btn btn-primary" value="Configurar">
                    <a href="{{route('problemas.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
