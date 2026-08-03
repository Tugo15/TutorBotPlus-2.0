@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Informe de Problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Informe del Problema "'.$problema_estadistica->nombre.'"'])
    @include('informes.componentes.estadisticas_problemas', ['problema_estadistica'=>$problema_estadistica])
    @include('informes.componentes.graficas', ['estadistica_estados'=>$estadistica_estados])
    @include('informes.componentes.tabla_estudiantes', ['envios'=>$envios])
    
@endsection
