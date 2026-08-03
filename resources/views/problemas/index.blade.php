@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Problemas'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Problemas</h6>
                        <a class="btn btn-primary active" href="{{ route('problemas.crear') }}">Crear</a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Categorías
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cursos
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Lenguajes
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        ¿Visible?
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        ¿LLM Activado?
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Límite de uso de LLM
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Fecha Inicio
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Fecha Termino
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar problemas', 'eliminar problemas'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($problemas as $problema)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $problema->nombre }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                @foreach ($problema->categorias()->get() as $categoria)
                                                    <div class="col-auto">
                                                        <span
                                                            class="badge bg-gradient-secondary">{{ $categoria->nombre }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                @foreach ($problema->cursos()->get() as $curso)
                                                    <div class="col-auto">
                                                        <span
                                                            class="badge bg-gradient-secondary">{{ $curso->codigo }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                @foreach ($problema->lenguajes()->get()->pluck('abreviatura')->unique() as $lenguaje)
                                                    <div class="col-auto">
                                                        <span
                                                            class="badge bg-gradient-secondary">{{ $lenguaje }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $problema->visible == true ? 'Si' : 'No' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $problema->habilitar_llm == true ? 'Si' : 'No' }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $problema->limite_llm }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $problema->fecha_inicio ? $problema->fecha_inicio : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $problema->fecha_termino ? $problema->fecha_termino : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $problema->created_at ? $problema->creado : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar problemas', 'eliminar problemas'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('ver informe del problema')
                                                        <a class="btn btn-outline-warning"
                                                        href="{{ route('informes.problemas.index', ['id' => $problema->id]) }}">Informe</a>
                                                    @endcan
                                                    @can('editar problemas')
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('problemas.editar_config_llm', ['id' => $problema->id]) }}">LLM</a>
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('problemas.editorial', ['id' => $problema->id]) }}">Editorial</a>
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('casos_pruebas.assign', ['id' => $problema->id]) }}">Casos
                                                            de Prueba</a>
                                                        <a class="btn btn-outline-warning"
                                                            href="{{ route('problemas.editar', ['id' => $problema->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar problemas')
                                                        <form action="{{ route('problemas.eliminar', ['id' => $problema->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el problema '.$problema->nombre}}', {{$problema->id}})" id="eliminarForm_{{$problema->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger"><i
                                                                    class="fa fa-fw fa-trash"></i></button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush
