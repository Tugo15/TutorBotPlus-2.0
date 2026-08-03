@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Informe de Evaluación'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Informe de la Evaluación "'.$certamen_estadistica->nombre.'"'])
    @include('informes.componentes.estadisticas_certamen', ['certamen_estadistica'=>$certamen_estadistica])
    @include('informes.componentes.graficas', ['estadistica_estados'=>$estadistica_estados])
    @include('informes.componentes.tabla_estudiantes_certamen', ['listado_resultados'=>$listado_resultados])
    
@endsection
