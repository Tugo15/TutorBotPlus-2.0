@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Informe del Curso'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Informe del Curso "'.$curso_estadistica->nombre.'"'])
    @include('informes.componentes.estadisticas_cursos', ['curso_estadistica'=>$curso_estadistica])
    @include('informes.componentes.informe_problemas_curso')
    @include('informes.componentes.graficas_cursos', ['estadistica_estados'=>$estadistica_estados])
    @include('informes.componentes.tabla_estudiantes_cursos', ['listado_estudiantes'=>$listado_estudiantes])
@endsection
