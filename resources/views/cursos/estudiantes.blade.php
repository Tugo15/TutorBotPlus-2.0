@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Estudiantes del Curso'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Gestión de Estudiantes'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card shadow-xs border mb-4">
                <div class="card-header pb-0 border-bottom mb-0">
                    <div class="row align-items-center pb-2 gy-2">
                        <div class="col-12 col-lg">
                            <a href="{{ route('cursos.index') }}" class="btn btn-xs btn-outline-secondary mb-0">
                                <i class="fa fa-arrow-left me-1"></i> Volver a Cursos
                            </a>
                        </div>
                        <div class="col-12 col-lg-auto text-center">
                            <h6 class="mb-0 font-weight-bold text-dark">
                                <i class="fa fa-users me-2 text-primary"></i>Estudiantes del Curso: <span class="text-primary">{{ $curso->nombre }}</span>
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                Código: <strong>{{ $curso->codigo }}</strong> | Total Matriculados: <span class="badge bg-primary text-xxs">{{ $estudiantes->count() }}</span>
                            </p>
                        </div>
                        <div class="col-12 col-lg d-flex justify-content-center justify-content-lg-end align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text"><i class="fa fa-search text-xs"></i></span>
                                <input type="text" class="form-control" id="buscadorEstudiantesCurso" placeholder="Filtrar matriculados...">
                            </div>
                            <button type="button" class="btn btn-sm btn-dark mb-0" data-bs-toggle="modal" data-bs-target="#modalInscribirEstudiantes">
                                <i class="fa fa-user-plus me-1"></i> Inscribir Estudiantes
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-0 d-none" id="btnSacarSeleccionados" onclick="confirmarSacarSeleccionados()">
                                <i class="fa fa-user-times me-1"></i> Sacar Seleccionados (<span id="countSeleccionados">0</span>)
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
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show mx-4 mt-3 mb-0 text-dark" role="alert">
                            <span>{{ session('warning') }}</span>
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

                    <form id="formSacarMasivo" action="{{ route('cursos.estudiantes.eliminar', ['id' => $curso->id]) }}" method="POST">
                        @csrf
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="tablaEstudiantesCurso">
                                <thead>
                                    <tr class="border-bottom">
                                        <th class="text-center text-secondary text-xxs font-weight-bold opacity-7" style="width: 40px;">
                                            <input type="checkbox" id="checkAllEstudiantes" title="Seleccionar todos">
                                        </th>
                                        <th class="text-secondary text-xxs font-weight-bold opacity-7 ps-3">Estudiante</th>
                                        <th class="text-center text-secondary text-xxs font-weight-bold opacity-7">RUT</th>
                                        <th class="text-center text-secondary text-xxs font-weight-bold opacity-7">Correo</th>
                                        <th class="text-center text-secondary text-xxs font-weight-bold opacity-7">Rol</th>
                                        <th class="text-center text-secondary text-xxs font-weight-bold opacity-7">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($estudiantes as $estudiante)
                                        <tr class="fila-estudiante-curso" data-nombre="{{ $estudiante->lastname }} {{ $estudiante->firstname }} {{ $estudiante->username }} {{ $estudiante->email }}" data-rut="{{ preg_replace('/[^0-9kK]/', '', $estudiante->rut ?? '') }}">
                                            <td class="text-center align-middle">
                                                <input type="checkbox" name="user_ids[]" value="{{ $estudiante->id }}" class="check-estudiante" onchange="actualizarBotonSacar()">
                                            </td>
                                            <td class="align-middle ps-3 py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs bg-gradient-dark me-2 rounded-circle text-center d-flex align-items-center justify-content-center">
                                                        <span class="text-xxs text-white font-weight-bold">{{ strtoupper(substr($estudiante->firstname, 0, 1)) }}</span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-xs font-weight-bold text-dark">{{ $estudiante->lastname }}, {{ $estudiante->firstname }}</h6>
                                                        <span class="text-xxs text-secondary">{{ $estudiante->username }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center py-2">
                                                <span class="badge border border-secondary text-secondary bg-white text-xxs font-monospace">{{ $estudiante->rut ?: '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center py-2">
                                                <span class="text-xs text-secondary">{{ $estudiante->email }}</span>
                                            </td>
                                            <td class="align-middle text-center py-2">
                                                @if($estudiante->roles->count() <= 1)
                                                    <span class="badge {{ optional($estudiante->roles->first())->name == 'profesor' ? 'bg-primary' : 'bg-light text-dark' }} text-xxs">
                                                        {{ ucfirst(optional($estudiante->roles->first())->name ?? 'Estudiante') }}
                                                    </span>
                                                @else
                                                    <button type="button" class="btn btn-xs btn-outline-info mb-0 d-inline-flex align-items-center"
                                                            onclick="verEtiquetasModal('Roles de {{ addslashes($estudiante->firstname . ' ' . $estudiante->lastname) }}', {{ json_encode($estudiante->roles->pluck('name')) }}, 'info', 'fa-shield-alt')"
                                                            title="Ver roles asignados">
                                                        <i class="fa fa-shield-alt me-1"></i> Ver ({{ $estudiante->roles->count() }})
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center py-2">
                                                <button type="button" class="btn btn-xs btn-outline-danger mb-0" 
                                                        title="Sacar a este estudiante del curso"
                                                        onclick="confirmarSacarIndividual({{ $estudiante->id }}, '{{ addslashes($estudiante->firstname . ' ' . $estudiante->lastname) }}')">
                                                    <i class="fa fa-user-times me-1"></i> Sacar del curso
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-xs text-secondary">
                                                <i class="fa fa-users fa-2x text-muted mb-2 d-block"></i>
                                                No hay estudiantes matriculados en este curso aún.
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-xs btn-dark" data-bs-toggle="modal" data-bs-target="#modalInscribirEstudiantes">
                                                        <i class="fa fa-user-plus me-1"></i> Inscribir Estudiantes Ahora
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr id="filaSinResultadosMatriculados" class="d-none">
                                        <td colspan="6" class="text-center py-4 text-xs text-secondary">
                                            <i class="fa fa-search text-muted fa-2x mb-2 d-block"></i>
                                            No se encontraron estudiantes matriculados que coincidan con la búsqueda.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL INSCRIBIR ESTUDIANTES (3 FORMAS SÚPER RÁPIDAS) -->
    <div class="modal fade" id="modalInscribirEstudiantes" tabindex="-1" aria-labelledby="modalInscribirLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title font-weight-bold" id="modalInscribirLabel">
                        <i class="fa fa-user-plus text-primary me-2"></i>Inscribir Estudiantes en {{ $curso->nombre }} ({{ $curso->codigo }})
                    </h6>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <!-- Nav Tabs para los 3 métodos -->
                    <ul class="nav nav-tabs nav-justified border-bottom bg-light px-3 pt-2" id="inscribirTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-xs font-weight-bold py-2" id="tab-lista-tab" data-bs-toggle="tab" data-bs-target="#tab-lista" type="button" role="tab">
                                <i class="fa fa-list-check me-1"></i> 1. Seleccionar de la Lista
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-xs font-weight-bold py-2" id="tab-masivo-tab" data-bs-toggle="tab" data-bs-target="#tab-masivo" type="button" role="tab">
                                <i class="fa fa-paste me-1"></i> 2. Pegar Lista (RUTs o Correos)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-xs font-weight-bold py-2" id="tab-copiar-tab" data-bs-toggle="tab" data-bs-target="#tab-copiar" type="button" role="tab">
                                <i class="fa fa-copy me-1"></i> 3. Copiar de Otro Curso
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-3" id="inscribirTabsContent">
                        <!-- PESTAÑA 1: SELECTOR DE LISTA CON BÚSQUEDA -->
                        <div class="tab-pane fade show active" id="tab-lista" role="tabpanel">
                            <form action="{{ route('cursos.estudiantes.agregar', ['id' => $curso->id]) }}" method="POST">
                                @csrf
                                <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                    <div class="input-group input-group-sm" style="max-width: 320px;">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" class="form-control" id="buscadorDisponibles" placeholder="Buscar por nombre, apellido o RUT...">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary mb-0" id="btnMarcarTodosDisponibles">
                                            <i class="fa fa-check-double me-1"></i> Seleccionar todos los visibles
                                        </button>
                                    </div>
                                </div>

                                <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto;">
                                    @if($estudiantes_disponibles->isEmpty())
                                        <p class="text-xs text-secondary text-center py-3 mb-0">No hay otros estudiantes disponibles en la plataforma para inscribir.</p>
                                    @else
                                        <div class="list-group list-group-flush" id="listaDisponibles">
                                            @foreach($estudiantes_disponibles as $disp)
                                                <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 cursor-pointer item-estudiante-disp" data-nombre="{{ $disp->lastname }} {{ $disp->firstname }} {{ $disp->email }}" data-rut="{{ preg_replace('/[^0-9kK]/', '', $disp->rut ?? '') }}">
                                                    <div class="d-flex align-items-center">
                                                        <input class="form-check-input me-3 check-disp" type="checkbox" name="estudiantes[]" value="{{ $disp->id }}">
                                                        <div>
                                                            <strong class="text-xs text-dark">{{ $disp->lastname }}, {{ $disp->firstname }}</strong>
                                                            <span class="text-xxs text-muted d-block">{{ $disp->email }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge border border-secondary text-secondary bg-white text-xxs font-monospace">{{ $disp->rut ?: '-' }}</span>
                                                </label>
                                            @endforeach
                                            <div id="sinResultadosDisponibles" class="text-xs text-secondary text-center py-3 d-none">
                                                <i class="fa fa-info-circle me-1"></i> No se encontraron estudiantes que coincidan con la búsqueda.
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <span class="text-xs text-secondary">Seleccionados: <strong id="countDispSeleccionados">0</strong></span>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-light mb-0 me-1" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-xs btn-dark mb-0" id="btnSubmitLista" disabled>
                                            <i class="fa fa-check me-1"></i> Inscribir Seleccionados
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- PESTAÑA 2: PEGADO MASIVO DE RUTS O CORREOS -->
                        <div class="tab-pane fade" id="tab-masivo" role="tabpanel">
                            <form action="{{ route('cursos.estudiantes.agregar_masivo', ['id' => $curso->id]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label text-xs font-weight-bold text-dark">
                                        Pega aquí la lista de RUTs o Correos Institucionales:
                                    </label>
                                    <textarea name="lista_estudiantes" class="form-control font-monospace text-xs" rows="8" 
                                              placeholder="Ejemplo:&#10;20123456-7&#10;19.876.543-2&#10;estudiante1@ucsc.cl&#10;estudiante2@ucsc.cl" required></textarea>
                                    <small class="text-xxs text-muted mt-1 d-block">
                                        <i class="fa fa-info-circle me-1"></i> Puedes pegar directamente columnas desde Excel. Acepta un estudiante por línea, o separados por coma o punto y coma.
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <button type="button" class="btn btn-xs btn-light mb-0" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-xs btn-dark mb-0">
                                        <i class="fa fa-upload me-1"></i> Inscribir Lista Masiva
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- PESTAÑA 3: COPIAR DE OTRO CURSO -->
                        <div class="tab-pane fade" id="tab-copiar" role="tabpanel">
                            <form action="{{ route('cursos.estudiantes.copiar_curso', ['id' => $curso->id]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label text-xs font-weight-bold text-dark">
                                        Selecciona el curso desde el cual deseas copiar los estudiantes:
                                    </label>
                                    <select name="id_curso_origen" class="form-select form-select-sm text-xs" required>
                                        <option value="" disabled selected>-- Elige un curso de origen --</option>
                                        @foreach($otros_cursos as $oCurso)
                                            <option value="{{ $oCurso->id }}">
                                                {{ $oCurso->nombre }} ({{ $oCurso->codigo }}) — {{ $oCurso->users_count }} estudiante(s)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-xxs text-muted mt-1 d-block">
                                        <i class="fa fa-info-circle me-1"></i> Todos los estudiantes matriculados en el curso de origen se inscribirán también a este curso (no se duplicarán los que ya estén).
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <button type="button" class="btn btn-xs btn-light mb-0" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-xs btn-dark mb-0">
                                        <i class="fa fa-copy me-1"></i> Copiar Estudiantes a este Curso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULARIO OCULTO PARA SACAR UN SOLO ESTUDIANTE -->
    <form id="formSacarIndividual" action="{{ route('cursos.estudiantes.eliminar', ['id' => $curso->id]) }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="user_id" id="inputUserIdIndividual" value="">
    </form>
@endsection

@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}?v={{ @filemtime(public_path('assets/js/DataTables/gestion_initialize_es_cl.js')) ?: '1.0' }}"></script>
    <script src="{{ asset('assets/js/alertas_administracion.js') }}?v={{ @filemtime(public_path('assets/js/alertas_administracion.js')) ?: '1.0' }}"></script>

    <script>
        // Check all en la tabla principal
        document.getElementById('checkAllEstudiantes')?.addEventListener('change', function() {
            const checks = document.querySelectorAll('.check-estudiante');
            checks.forEach(c => c.checked = this.checked);
            actualizarBotonSacar();
        });

        function actualizarBotonSacar() {
            const checks = document.querySelectorAll('.check-estudiante:checked');
            const btn = document.getElementById('btnSacarSeleccionados');
            const countSpan = document.getElementById('countSeleccionados');
            if (btn && countSpan) {
                countSpan.textContent = checks.length;
                if (checks.length > 0) {
                    btn.classList.remove('d-none');
                } else {
                    btn.classList.add('d-none');
                }
            }
        }

        function confirmarSacarIndividual(userId, nombre) {
            Swal.fire({
                title: '¿Sacar estudiante del curso?',
                text: 'Se desmatriculará a "' + nombre + '" del curso {{ addslashes($curso->nombre) }}.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, sacar del curso',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('inputUserIdIndividual').value = userId;
                    document.getElementById('formSacarIndividual').submit();
                }
            });
        }

        function confirmarSacarSeleccionados() {
            const checks = document.querySelectorAll('.check-estudiante:checked');
            if (checks.length === 0) return;

            Swal.fire({
                title: '¿Sacar ' + checks.length + ' estudiante(s)?',
                text: 'Se desmatriculará a los estudiantes seleccionados del curso {{ addslashes($curso->nombre) }}.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desmatricular seleccionados',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formSacarMasivo').submit();
                }
            });
        }

        // Helpers de normalización para búsquedas insensibles a tildes y formatos de RUT
        function normalizarTexto(str) {
            if (!str) return '';
            return str.toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        function normalizarRut(str) {
            if (!str) return '';
            return str.toString().toLowerCase().replace(/[^0-9k]/g, '');
        }

        // Filtro en tabla principal de estudiantes matriculados
        const inputBuscarCurso = document.getElementById('buscadorEstudiantesCurso');
        if (inputBuscarCurso) {
            ['input', 'paste', 'keyup'].forEach(evento => {
                inputBuscarCurso.addEventListener(evento, function() {
                    const rawVal = this.value || '';
                    const termTexto = normalizarTexto(rawVal);
                    const termRut = normalizarRut(rawVal);
                    const filas = document.querySelectorAll('.fila-estudiante-curso');
                    let visibles = 0;

                    filas.forEach(function(fila) {
                        const nombreData = normalizarTexto(fila.getAttribute('data-nombre') || fila.textContent);
                        const rutData = normalizarRut(fila.getAttribute('data-rut') || '');

                        const coincide = !termTexto || nombreData.includes(termTexto) || (termRut && rutData.includes(termRut));
                        if (coincide) {
                            fila.style.display = '';
                            visibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    });

                    const filaSinRes = document.getElementById('filaSinResultadosMatriculados');
                    if (filaSinRes) {
                        filaSinRes.classList.toggle('d-none', visibles > 0 || filas.length === 0);
                    }
                });
            });
        }

        // Filtro interactivo en el modal para lista de disponibles
        const buscadorDisp = document.getElementById('buscadorDisponibles');
        if (buscadorDisp) {
            ['input', 'paste', 'keyup'].forEach(evento => {
                buscadorDisp.addEventListener(evento, function() {
                    const rawVal = this.value || '';
                    const termTexto = normalizarTexto(rawVal);
                    const termRut = normalizarRut(rawVal);
                    const items = document.querySelectorAll('#listaDisponibles .item-estudiante-disp');
                    let visibles = 0;

                    items.forEach(function(item) {
                        const nombreData = normalizarTexto(item.getAttribute('data-nombre') || item.textContent);
                        const rutData = normalizarRut(item.getAttribute('data-rut') || '');

                        const coincide = !termTexto || nombreData.includes(termTexto) || (termRut && rutData.includes(termRut));
                        if (coincide) {
                            item.style.display = '';
                            visibles++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    const sinRes = document.getElementById('sinResultadosDisponibles');
                    if (sinRes) {
                        sinRes.classList.toggle('d-none', visibles > 0 || items.length === 0);
                    }
                });
            });
        }

        // Contar seleccionados en modal pestaña 1
        document.querySelectorAll('.check-disp').forEach(function(ch) {
            ch.addEventListener('change', actualizarContadorDisp);
        });

        function actualizarContadorDisp() {
            const checks = document.querySelectorAll('.check-disp:checked');
            const countSpan = document.getElementById('countDispSeleccionados');
            const btnSubmit = document.getElementById('btnSubmitLista');
            if (countSpan) countSpan.textContent = checks.length;
            if (btnSubmit) btnSubmit.disabled = checks.length === 0;
        }

        document.getElementById('btnMarcarTodosDisponibles')?.addEventListener('click', function() {
            const visibleItems = document.querySelectorAll('#listaDisponibles .item-estudiante-disp');
            visibleItems.forEach(function(item) {
                if (item.style.display !== 'none') {
                    const ch = item.querySelector('.check-disp');
                    if (ch) ch.checked = true;
                }
            });
            actualizarContadorDisp();
        });
    </script>
@endpush

