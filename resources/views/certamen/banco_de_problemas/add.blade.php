@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Gestión de Banco de Problemas'])

@section('content')
    @include('layouts.navbars.auth.topnav', [
        'title' => $certamen->nombre . ' - Banco de Problemas',
    ])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Banco de Problemas</h6>
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

                    <form action="{{ route('certamen.add_categoria', ['id_certamen' => $certamen->id]) }}" method="POST" id="submitForm">
                        @csrf
                        @include('certamen.banco_de_problemas.form')
                    </form>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th>Categoria
                                    </th>
                                    </th>
                                    @canany(['editar certamen'])
                                        <th>
                                            Acción</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($banco_categorias as $categoria)
                                    <tr>
                                        <td>
                                            <h6>{{ $categoria->nombre }}
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                @can('editar certamen')
                                                    <form action="{{ route('certamen.eliminar_categoria', ['id_certamen' => $certamen->id, 'id_categoria'=>$categoria->id_categoria]) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger delete_button"><i
                                                                class="fa fa-fw fa-trash" onclick="deactivate_buttons()"></i></button>
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
    @include('certamen.banco_de_problemas.ayuda')
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
    
    <script>
        function deactivate_buttons(){
            document.getElementById('add_button').setAttribute('disabled', true);
            var delete_buttons = document.querySelectorAll('.delete_button');
            for(let i=0; i<delete_buttons.length; i++){
                delete_buttons[i].setAttribute('disabled', true);
            }
        }
        document.querySelector('#submitForm').addEventListener('submit', e => {
            deactivate_buttons()
        });

    </script>
@endpush
