@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Configurar LLM de un problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Problema '.$problema->codigo.' - Configurar Large Language Model'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('problemas.configurar_llm', ['id'=>$problema->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-robot me-2"></i>Configuración de LLM (IA): {{ $problema->nombre }}</h6>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('problemas.form_llm')
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-save me-1"></i> Guardar Configuración LLM</button>
                        <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
