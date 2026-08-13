@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Gestión de Usuarios'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Usuarios'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users me-2 text-primary"></i>Gestión de Usuarios</h6>
                            <p class="text-xs text-secondary mb-0">Administre los usuarios registrados, asigne roles, edite información y gestione accesos.</p>
                        </div>
                        @can('crear usuario')
                        <div>
                            <a class="btn btn-sm btn-primary mb-0 me-2" href="{{ route('usuarios.crear') }}"><i class="fa fa-user-plus me-1"></i> Crear Usuario</a>
                            <a class="btn btn-sm btn-outline-primary mb-0" href="{{ route('usuarios.bulk') }}"><i class="fa fa-file-upload me-1"></i> Inserción Masiva</a>
                        </div>
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
                    @php
                        $roles_disponibles = collect();
                        $cursos_disponibles = collect();
                        foreach($users as $usr) {
                            foreach($usr->getRoleNames() as $rol) { $roles_disponibles->put($rol, $rol); }
                            foreach($usr->cursos as $cur) { $cursos_disponibles->put($cur->codigo, $cur->codigo); }
                        }
                        $roles_disponibles = $roles_disponibles->sort();
                        $cursos_disponibles = $cursos_disponibles->sort();
                    @endphp

                    <div id="custom-filters-src" class="d-none">
                        <div class="d-flex align-items-center">
                            <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Rol:</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    Seleccionar...
                                </button>
                                <ul class="dropdown-menu px-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($roles_disponibles as $r)
                                        <li>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input filter-rol-chk" type="checkbox" value="{{ $r }}" id="chkRol_{{ $loop->index }}">
                                                <label class="form-check-label text-sm mb-0" for="chkRol_{{ $loop->index }}">{{ $r }}</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <label class="me-2 mb-0 font-weight-bold text-xs text-uppercase text-secondary">Curso:</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary bg-white btn-sm dropdown-toggle mb-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    Seleccionar...
                                </button>
                                <ul class="dropdown-menu px-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($cursos_disponibles as $c)
                                        <li>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input filter-curso-chk" type="checkbox" value="{{ $c }}" id="chkCur_{{ $loop->index }}">
                                                <label class="form-check-label text-sm mb-0" for="chkCur_{{ $loop->index }}">{{ $c }}</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Usuario
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Roles
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cursos
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Email
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creado</th>
                                    @canany(['editar usuario', 'eliminar usuario'])
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Acciones</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-inline-flex align-items-center py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <h6 class="mb-0 text-sm font-weight-bold">{{ $user->firstname ? $user->firstname . ' ' . $user->lastname : $user->username }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $user->username }} | {{ $user->rut }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($user->getRoleNames() as $rol)
                                                    <span class="badge bg-gradient-info text-xs">{{ $rol }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($user->cursos()->get() as $curso)
                                                    <span class="badge bg-gradient-primary text-xs" title="{{ $curso->nombre }}">{{ $curso->codigo }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->email }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <p class="text-sm font-weight-bold mb-0">
                                                {{ $user->fecha ? $user->fecha : 'Desconocido' }}</p>
                                        </td>
                                        @canany(['editar usuario', 'eliminar usuario'])
                                            <td class="align-middle text-end">
                                                <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                    @can('editar usuario')
                                                        <a class="btn btn-sm btn-outline-warning me-1" title="Editar Usuario"
                                                            href="{{ route('usuarios.editar', ['id' => $user->id]) }}"><i
                                                                class="fa fa-pencil"></i></a>
                                                    @endcan
                                                    @can('eliminar usuario')
                                                        @if (auth()->user()->id != $user->id)
                                                            <form action="{{ route('usuarios.eliminar', ['id' => $user->id]) }}"
                                                                method="POST" onsubmit="event.preventDefault();submitFormEliminar('el usuario {{ $user->username }}', {{ $user->id }})" id="eliminarForm_{{ $user->id }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Usuario"><i
                                                                        class="fa fa-fw fa-trash"></i></button>
                                                            </form>
                                                        @endif
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
<link href="{{asset('assets/js/DataTables/datatables.min.css')}}" rel="stylesheet">
 
<script src="{{asset('assets/js/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/DataTables/gestion_initialize_es_cl.js')}}"></script>
<script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table')) {
                var table = $('#table').DataTable();
                
                $('#custom-filters-src').children().appendTo('.custom-filters-container');
                $('#custom-filters-src').remove();
                
                $('.filter-rol-chk').on('change', function() {
                    var roles = $('.filter-rol-chk:checked').map(function() { return this.value; }).get();
                    var regex = roles.length > 0 ? '(' + roles.join('|') + ')' : '';
                    table.column(1).search(regex, true, false).draw();
                });
                
                $('.filter-curso-chk').on('change', function() {
                    var cursos = $('.filter-curso-chk:checked').map(function() { return this.value; }).get();
                    var regex = cursos.length > 0 ? '(' + cursos.join('|') + ')' : '';
                    table.column(2).search(regex, true, false).draw();
                });
            }
        }, 500);
    });
</script>
@endpush