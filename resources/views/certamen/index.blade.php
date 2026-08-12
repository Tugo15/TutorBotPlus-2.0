@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Evaluaciones'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Evaluaciones'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-check me-2 text-primary"></i>Gestión de Evaluaciones y Certámenes</h6>
                            <p class="text-xs text-secondary mb-0">Administre las evaluaciones programadas, certámenes, fechas de vigencia y duplicación entre secciones.</p>
                        </div>
                        @can('crear certamen')
                        <a class="btn btn-sm btn-primary mb-0" href="{{ route('certamen.crear') }}"><i class="fa fa-plus me-1"></i> Crear Evaluación</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('error') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 mb-0 text-white" role="alert">
                            <span>{{ session('success') }}</span>
                            <button type="button" class="btn-close text-lg opacity-10 py-3" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Nombre
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Curso
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Inicio
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Término
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar certamen', 'eliminar certamen', 'ver informe del certamen'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($certamenes as $certamen)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-sm font-weight-bold ps-2">{{ $certamen->nombre }}</h6>
                                        </td>
                                        <td>
                                            <span class="badge bg-gradient-primary text-xs">{{ $certamen->curso->nombre }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $certamen->fecha_inicio ? $certamen->fecha_inicio : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ $certamen->fecha_termino ? $certamen->fecha_termino : 'No Definido' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $certamen->created_at ? $certamen->creado : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar problemas', 'eliminar problemas', 'editar certamen', 'crear certamen'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('ver informe del certamen')
                                                    <a class="btn btn-sm btn-outline-warning me-1" title="Ver Informe"
                                                            href="{{ route('informe.certamen', ['id_certamen' => $certamen->id]) }}">Informe</a>
                                                    @endcan
                                                    @can('editar certamen')
                                                    <a class="btn btn-sm btn-outline-warning me-1" title="Banco de Problemas"
                                                            href="{{ route('certamen.banco_problemas', ['id_certamen' => $certamen->id]) }}">Banco</a>
                                                        <a class="btn btn-sm btn-outline-warning me-1" title="Editar Evaluación"
                                                            href="{{ route('certamen.editar', ['id' => $certamen->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('crear certamen')
                                                        <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#duplicarModal_{{ $certamen->id }}" title="Duplicar Evaluación">
                                                            <i class="fa fa-clone"></i>
                                                        </button>
                                                    @endcan
                                                    @can('eliminar certamen')
                                                        <form action="{{ route('certamen.eliminar', ['id' => $certamen->id]) }}"
                                                            method="POST" onsubmit="event.preventDefault();submitFormEliminar('{{'el certamen '.$certamen->nombre}}', {{$certamen->id}})" id="eliminarForm_{{$certamen->id}}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Evaluación"><i
                                                                    class="fa fa-fw fa-trash"></i></button>
                                                        </form>
                                                    @endcan
                                                </div>

                                                @can('crear certamen')
                                                <!-- Modal Duplicar Certamen -->
                                                <div class="modal fade text-start" id="duplicarModal_{{ $certamen->id }}" tabindex="-1" aria-labelledby="duplicarModalLabel_{{ $certamen->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('certamen.duplicar') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="id_certamen" value="{{ $certamen->id }}">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="duplicarModalLabel_{{ $certamen->id }}">Duplicar Evaluación</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-sm text-secondary mb-3">Se creará una copia de <strong>{{ $certamen->nombre }}</strong> y todas sus categorías asociadas. Seleccione el curso o sección de destino:</p>
                                                                    <div class="form-group">
                                                                        <label for="id_curso_{{ $certamen->id }}" class="form-control-label font-weight-bold">Curso / Sección Destino</label>
                                                                        <select class="form-control" name="id_curso" id="id_curso_{{ $certamen->id }}" required>
                                                                            @foreach ($cursos as $c)
                                                                                <option value="{{ $c->id }}" {{ $c->id == $certamen->curso->id ? 'selected' : '' }}>
                                                                                    {{ $c->nombre }} ({{ $c->codigo }})
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary">Duplicar Evaluación</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endcan
                                            </td>
                                        @endcanany
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
