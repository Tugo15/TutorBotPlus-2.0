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
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
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
                                            <h6 class="mb-0 text-sm">{{ $item->id }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->entradas }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->salidas }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $item->puntos }}</h6>

                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-sm">
                                                {{ $item->ejemplo ? 'Si' : 'No' }}</h6>
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('editar problemas')
                                                    <form action="{{ route('casos_pruebas.eliminar', ['id' => $item->id]) }}"
                                                        method="POST" onsubmit="deshabilitar_boton()">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger delete_button"><i
                                                                class="fa fa-fw fa-trash"></i></button>
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
            add_button.setAttribute('disabled', true);
            for(let i=0; i<delete_button.length; i++){
                delete_button[i].setAttribute('disabled', true);
            }
        }
    </script>
@endpush
