@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Inicio'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Inicio'])
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-body">
                <h5>Bienvenido {{auth()->user()->firstname}} {{auth()->user()->lastname}}</h5>
            </div>
        </div>
    </div>
@endsection