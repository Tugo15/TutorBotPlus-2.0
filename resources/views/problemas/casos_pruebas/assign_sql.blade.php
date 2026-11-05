@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Casos de Prueba'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->codigo . ' - Casos de Prueba SQL',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            @if(!empty($es_wizard))
                @include('problemas.componentes.wizard_creacion', ['paso' => 2, 'problema' => $problema, 'caso' => $caso])
            @endif
            <div class="card mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-2 w-100 flex-wrap gap-3">
                        <div class="d-none d-lg-block" style="flex: 1;">
                            <a href="{{ route('problemas.index') }}" class="btn btn-xs btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                        </div>
                        <div class="text-center" style="flex: 2;">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-database me-2 text-warning"></i>Evaluación SQL: {{ $problema->nombre }}</h6>
                            <p class="text-xs text-secondary mb-0">Configuración para Consultas (SELECT), Vistas (VIEW), Triggers y Funciones.</p>
                        </div>
                        <div class="d-none d-lg-flex justify-content-end" style="flex: 1;">
                            <button type="button" class="btn btn-xs btn-outline-info mb-0" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
                                <i class="fa fa-question-circle me-1"></i> Guía y Ejemplos
                            </button>
                        </div>
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

                    <!-- Guía rápida contextual -->
                    <div class="mx-4 mt-3 p-3 bg-light rounded border border-light-subtle">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa fa-lightbulb text-warning"></i>
                            <span class="text-xs font-weight-bold text-dark">Modo de Evaluación Inteligente SQL:</span>
                        </div>
                        <ul class="text-xs text-secondary mb-0 ps-3">
                            <li><strong>Consultas SELECT directas:</strong> Deje la <em>Consulta de Verificación</em> en blanco. El sistema ejecutará directamente la consulta del estudiante.</li>
                            <li><strong>Vistas (CREATE VIEW):</strong> Escriba en <em>Consulta de Verificación</em> el query que lee la vista (ej: <code>SELECT * FROM mi_vista ORDER BY id;</code>).</li>
                            <li><strong>Triggers o Modificaciones:</strong> Escriba en <em>Consulta de Verificación</em> las sentencias para disparar el trigger y verificar el cambio (ej: <code>INSERT INTO ...; SELECT ...;</code>).</li>
                        </ul>
                    </div>
                    
                    <form action="{{ route('casos_pruebas.set_sql', ['id' => $problema->id]) }}" method="POST">
                        @csrf
                        @if(isset($caso))
                            <input type="hidden" name="id_caso" value="{{ $caso->id }}">
                        @endif
                        <div class="row mx-3 mt-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="entradas" class="form-label font-weight-bold text-xs text-dark mb-0">
                                            Consulta / Script de Verificación (Entrada)
                                        </label>
                                        <span class="badge bg-secondary text-xxs font-weight-normal">Opcional para SELECT / Requerido para Vistas o Triggers</span>
                                    </div>
                                    <textarea class="form-control font-monospace text-xs @error('entradas') is-invalid @enderror" id="entradas" name="entradas" rows="3" placeholder="-- Para SELECT normal: DEJAR EN BLANCO&#10;-- Para VIEW: SELECT * FROM nombre_vista;&#10;-- Para TRIGGER: INSERT INTO t VALUES(...); SELECT campo FROM t;">{{ isset($caso) ? old('entradas', $caso->entradas) : old('entradas') }}</textarea>
                                    @error('entradas')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                    <small class="text-muted text-xxs">Si se define, este script se ejecutará <em>después</em> del código del estudiante y su resultado se comparará con la salida esperada.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="salidas" class="form-label font-weight-bold text-xs text-dark">
                                        Salida Esperada (Resultado de la Consulta) <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control font-monospace text-xs @error('salidas') is-invalid @enderror" id="salidas" name="salidas" rows="6" placeholder="Columna1|Columna2|Columna3&#10;Fila1_Val1|Fila1_Val2|Fila1_Val3&#10;Fila2_Val1|Fila2_Val2|Fila2_Val3">{{ isset($caso) ? old('salidas', $caso->salidas) : old('salidas') }}</textarea>
                                    @error('salidas')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                    <small class="text-muted text-xxs">Columnas separadas por tubería (<code>|</code>). Acepta coincidencia exacta y flexibilidad de formato (espacios/tabulaciones/mayúsculas).</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="puntos" class="form-label font-weight-bold text-xs text-dark">Puntos</label>
                                    <input type="number" step="any" class="form-control form-control-sm @error('puntos') is-invalid @enderror" id="puntos" name="puntos" placeholder="Ej. 100" value="{{ isset($caso) ? old('puntos', $caso->puntos) : old('puntos', 100) }}">
                                    @error('puntos')
                                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                                    @enderror
                                </div>
                                <div class="d-flex flex-column gap-2 pt-2">
                                    <button class="btn btn-sm btn-dark mb-0" type="submit">
                                        <i class="fa fa-save me-1"></i> Guardar Caso SQL
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info mb-0" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
                                        <i class="fa fa-eye me-1"></i> Ver Ejemplos Detallados
                                    </button>
                                    <a href="{{ route('problemas.index') }}" class="btn btn-sm btn-outline-secondary mb-0">
                                        <i class="fa fa-arrow-left me-1"></i> Volver a Problemas
                                    </a>
                                </div>

                                @if(isset($problema->archivo_adicional))
                                    <div class="mt-4 p-3 bg-gray-100 rounded border">
                                        <span class="text-xs font-weight-bold text-dark d-block mb-1">
                                            <i class="fa fa-paperclip me-1 text-primary"></i> Base de Datos Adjunta:
                                        </span>
                                        <span class="badge bg-gradient-success text-xxs mb-2">
                                            {{ $problema->archivo_adicional }}
                                        </span>
                                        <p class="text-xxs text-secondary mb-0">Esta base de datos se cargará automáticamente de forma aislada e independiente en cada evaluación.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if(isset($casos) && $casos->count() > 1)
                        <div class="mx-4 mt-4 pt-3 border-top">
                            <h6 class="text-xs font-weight-bold text-dark mb-2">Todos los Casos de Prueba Registrados ({{ $casos->count() }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-items-center mb-0">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="text-xxs font-weight-bolder opacity-7">ID</th>
                                            <th class="text-xxs font-weight-bolder opacity-7">Verificación (Entrada)</th>
                                            <th class="text-xxs font-weight-bolder opacity-7">Salida Esperada</th>
                                            <th class="text-xxs font-weight-bolder opacity-7">Puntos</th>
                                            <th class="text-xxs font-weight-bolder opacity-7 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($casos as $c)
                                            <tr>
                                                <td class="text-xs font-weight-bold ps-2">{{ $c->id }}</td>
                                                <td class="text-xs font-monospace text-truncate" style="max-width: 200px;">
                                                    {{ !empty($c->entradas) ? $c->entradas : '(Directo SELECT)' }}
                                                </td>
                                                <td class="text-xs font-monospace text-truncate" style="max-width: 250px;">
                                                    {{ $c->salidas }}
                                                </td>
                                                <td class="text-xs font-weight-bold">{{ $c->puntos }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('casos_pruebas.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que desea eliminar este caso?');">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $c->id }}">
                                                        <button type="submit" class="btn btn-xs btn-outline-danger mb-0 py-1 px-2">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                                
                </div>
            </div>
        </div>
    </div>
    @include('problemas.casos_pruebas.ejemplo_sql')
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
@endpush
