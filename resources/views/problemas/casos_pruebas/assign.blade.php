@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Casos de Prueba'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => 'Problema ' . $problema->codigo . ' - Casos de Prueba',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Casos de Prueba</h6>
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

                    <form action="{{ route('casos_pruebas.add', ['id' => $problema->id]) }}" method="POST" onsubmit="deshabilitar_boton()">
                        @csrf
                        @include('problemas.casos_pruebas.form')
                    </form>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Entradas
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Salidas
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Puntos
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ejemplo
                                    </th>
                                    @canany(['editar problemas'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($casos as $item)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-sm ps-3">{{ $item->id }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->entradas }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->salidas }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->puntos }}</h6>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->ejemplo ? 'bg-gradient-success' : 'bg-gradient-secondary' }} text-xs">
                                                {{ $item->ejemplo ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center gap-2">
                                                @can('editar problemas')
                                                    <button type="button" class="btn btn-sm btn-outline-warning edit_button mb-0" 
                                                        title="Editar Caso" data-bs-toggle="modal" data-bs-target="#modalEditarCaso{{ $item->id }}">
                                                        <i class="fa fa-fw fa-pencil-alt"></i>
                                                    </button>

                                                    <form action="{{ route('casos_pruebas.eliminar', ['id' => $item->id]) }}"
                                                        method="POST" onsubmit="deshabilitar_boton()" class="mb-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger delete_button mb-0" title="Eliminar Caso">
                                                            <i class="fa fa-fw fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('editar problemas')
        @foreach ($casos as $item)
            <!-- Modal Editar Caso #{{ $item->id }} -->
            <div class="modal fade text-start" id="modalEditarCaso{{ $item->id }}" tabindex="-1" aria-labelledby="modalEditarCasoLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-bolder" id="modalEditarCasoLabel{{ $item->id }}">
                                <i class="fa fa-edit me-2 text-warning"></i>Editar Caso de Prueba #{{ $item->id }}
                            </h5>
                            <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('casos_pruebas.update') }}" method="POST" onsubmit="deshabilitar_boton()">
                            @csrf
                            <input type="hidden" name="id_caso" value="{{ $item->id }}">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="entradas_{{ $item->id }}" class="form-label font-weight-bold">Entradas</label>
                                        <textarea class="form-control" id="entradas_{{ $item->id }}" name="entradas" rows="5" placeholder="Entradas del caso">{{ $item->entradas }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="salidas_{{ $item->id }}" class="form-label font-weight-bold">Salidas <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="salidas_{{ $item->id }}" name="salidas" rows="5" required placeholder="Salidas esperadas">{{ $item->salidas }}</textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="puntos_{{ $item->id }}" class="form-label font-weight-bold">Puntos</label>
                                        <input type="number" step="any" class="form-control" id="puntos_{{ $item->id }}" name="puntos" value="{{ $item->puntos }}" placeholder="Puntaje">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="ejemplo_{{ $item->id }}" name="ejemplo" value="1" {{ $item->ejemplo ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ejemplo_{{ $item->id }}">
                                                Es un caso de ejemplo (mostrar entradas y salidas en los resultados)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endcan

    @include('problemas.casos_pruebas.ejemplo')
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    <script>
        function deshabilitar_boton(){
            const add_button = document.getElementById('boton_crear');
            const delete_button = document.querySelectorAll('.delete_button');
            const edit_button = document.querySelectorAll('.edit_button');
            if(add_button) add_button.setAttribute('disabled', true);
            for(let i=0; i<delete_button.length; i++){
                delete_button[i].setAttribute('disabled', true);
            }
            for(let i=0; i<edit_button.length; i++){
                edit_button[i].setAttribute('disabled', true);
            }
        }
    </script>
@endpush
