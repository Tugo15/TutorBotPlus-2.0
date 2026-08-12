@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Inserción masiva de usuarios'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Inserción masiva de usuarios'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('usuarios.bulk_store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm" enctype="multipart/form-data">
            @csrf
            <div class="card shadow-xs border">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fa fa-file-upload me-2"></i>Inserción Masiva de Usuarios</h6>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary mb-0"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info text-white text-xs mb-3" role="alert">
                        <i class="fa fa-info-circle me-1"></i> Suba un archivo CSV o TXT delimitado por punto y coma (;) para registrar múltiples usuarios simultáneamente.
                    </div>
                    
                    <p class="text-sm">
                        La contraseña inicial de cada usuario será su RUT sin dígito verificador.
                        <br>
                        <a href="{{ asset('examples/ejemplo_bulk_usuarios.csv') }}" class="badge bg-gradient-primary text-xs mt-1" download>
                            <i class="fa fa-download me-1"></i> Descargar archivo CSV de ejemplo
                        </a>
                    </p>

                    <div class="mb-3">
                        <label for="csvFile" class="form-label font-weight-bold text-sm">Archivo CSV / TXT*</label>
                        <input class="form-control" type="file" id="csvFile" name="csvFile" accept=".csv,.txt" required>
                        <p class="text-xs text-secondary mt-1 mb-0"><small>Formatos aceptados: .csv, .txt</small></p>
                    </div>
                    @error('csvFile')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror

                    <div class="card bg-gray-100 border-0 p-3 my-3">
                        <h6 class="text-xs font-weight-bold text-uppercase text-dark mb-2">
                            <i class="fa fa-exclamation-triangle text-warning me-1"></i> Estructura Requerida de Columnas (Separadas por punto y coma ;)
                        </h6>
                        <ol class="text-xs mb-0 ps-3">
                            <li class="mb-1"><strong>Nombre de Usuario</strong> (ej. jmacias)</li>
                            <li class="mb-1"><strong>Nombre</strong> (ej. Juan)</li>
                            <li class="mb-1"><strong>Apellido</strong> (ej. Macías)</li>
                            <li class="mb-1"><strong>Correo Electrónico</strong> (ej. jmacias@ejemplo.cl)</li>
                            <li class="mb-1"><strong>RUT</strong> (ej. 12345678-9)</li>
                            <li class="mb-1"><strong>Códigos de Cursos</strong> (separados por coma dentro del campo, ej. INF-101,INF-102)</li>
                            <li class="mb-1"><strong>Nombres de Roles</strong> (separados por coma dentro del campo, ej. estudiante,tutor)</li>
                        </ol>
                        <p class="text-xs text-danger font-weight-bold mt-2 mb-0">⚠️ Nota: El archivo NO debe incluir fila de encabezados.</p>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fa fa-upload me-1"></i> Procesar e Insertar Usuarios</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Volver</a>
                    </div>
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush