<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
    <div>
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-alt text-warning me-2"></i>Información de la Evaluación</h6>
        <p class="text-xs text-secondary mb-0">Configure los parámetros, fechas y curso al que pertenece la evaluación.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-12">
        <div class="form-group has-danger mb-3">
            <label for="nombre" class="form-control-label font-weight-bold text-sm @error('nombre') is-invalid @enderror">Nombre de la Evaluación*</label>
            <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Ej. Certamen #1 - Algoritmos y Estructuras"
                value="{{ isset($certamen) ? old('nombre', $certamen->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="fecha_inicio" class="form-control-label font-weight-bold text-sm @error('fecha_inicio') is-invalid @enderror">Fecha de Inicio*</label>
            <input class="form-control" type="datetime-local" name="fecha_inicio" id="fecha_inicio" value="{{isset($certamen)? old('fecha_inicio', $certamen->fecha_inicio) : old('fecha_inicio')}}">
            @error('fecha_inicio')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <label for="fecha_termino" class="form-control-label font-weight-bold text-sm @error('fecha_termino') is-invalid @enderror">Fecha de Término*</label>
            <input class="form-control" type="datetime-local" name="fecha_termino" id="fecha_termino" value="{{isset($certamen)? old('fecha_termino', $certamen->fecha_termino) : old('fecha_termino')}}">
            @error('fecha_termino')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 mb-3">
        <div class="form-group" style="position: relative; z-index: 9999;">
            <label for="descripcion" class="form-control-label font-weight-bold text-sm">Descripción del Certamen*</label>
            <input type="hidden" id="descripcion" name="descripcion"
                value="{{ isset($certamen) ? old('descripcion', $certamen->descripcion) : old('descripcion') }}">
            <div class="flex flex-col space-y-2">
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" style="position: relative; z-index: 9999;"></div>
            </div>
        </div>
        @error('descripcion')
            <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        @if (isset($id_curso_preseleccionado) && $id_curso_preseleccionado)
            @php
                $curso_fijo = $cursos->firstWhere('id', $id_curso_preseleccionado);
            @endphp
            <div class="form-group">
                <label class="form-control-label font-weight-bold text-sm">Curso Perteneciente*</label>
                <input type="hidden" name="curso" value="{{ $id_curso_preseleccionado }}">
                <div class="form-control bg-gray-100 d-flex align-items-center justify-content-between border" style="background-color: #e9ecef; cursor: not-allowed;">
                    <span class="font-weight-bold text-dark"><i class="fa fa-folder text-warning me-2"></i>{{ $curso_fijo->nombre ?? 'Curso Seleccionado' }}</span>
                    <span class="badge bg-primary text-xxs">{{ $curso_fijo->codigo ?? '' }}</span>
                </div>
            </div>
        @else
            <div class="form-group">
                <label for="curso" class="form-control-label font-weight-bold text-sm">Curso Perteneciente*</label>
                <select class="form-select" id="curso" name="curso" required>
                    <option value="" disabled {{ !isset($id_curso_preseleccionado) && !isset($certamen) ? 'selected' : '' }}>Selecciona un curso</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" @if (
                            (isset($certamen) && $certamen->curso->id == $curso->id) ||
                            (old('curso') == $curso->id)) selected @endif>
                            {{ $curso->nombre }} ({{ $curso->codigo }})</option>
                    @endforeach
                </select>
            </div>
        @endif
        @error('curso')
            <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <div class="form-group has-danger">
            <label for="penalizacion_error" class="form-control-label font-weight-bold text-sm @error('penalizacion_error') is-invalid @enderror">Penalización por Error</label>
            <input class="form-control" type="number" id="penalizacion_error" name="penalizacion_error" placeholder="Ej. 0.5" min="0" step="0.01"
                value="{{ isset($certamen) ? old('penalizacion_error', $certamen->penalizacion_error) : old('penalizacion_error', 0) }}">
            @error('penalizacion_error')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="form-group has-danger">
            <label for="cantidad_penalizacion" class="form-control-label font-weight-bold text-sm @error('cantidad_penalizacion') is-invalid @enderror">Máx. Penalizaciones</label>
            <input class="form-control" type="number" id="cantidad_penalizacion" name="cantidad_penalizacion" placeholder="Ej. 3" min="0"
                value="{{ isset($certamen) ? old('cantidad_penalizacion', $certamen->cantidad_penalizacion) : old('cantidad_penalizacion', 0) }}">
            @error('cantidad_penalizacion')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <h6 class="mb-1 text-sm font-weight-bold text-dark"><i class="fa fa-shield-alt text-primary me-2"></i>Restricciones de Acceso y Seguridad de Red</h6>
                <p class="text-xs text-secondary mb-3">Defina si los estudiantes pueden rendir la evaluación desde cualquier lugar o únicamente desde la red institucional.</p>
                
                @php
                    $isRestringido = isset($certamen) ? old('restriccion_red', $certamen->restriccion_red) : old('restriccion_red', 0);
                @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="selection-card w-100 mb-0 @if(!$isRestringido) checked @endif" for="restriccion_libre">
                            <input type="radio" id="restriccion_libre" name="restriccion_red" value="0" @if(!$isRestringido) checked @endif onchange="toggleIpOptions(false)">
                            <div class="selection-card-content">
                                <div class="selection-card-title"><i class="fa fa-globe text-success me-1"></i> Acceso Libre</div>
                                <div class="selection-card-subtitle">Permitir rendir la evaluación desde cualquier red o ubicación (Casa, Universidad, Celular).</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="selection-card w-100 mb-0 @if($isRestringido) checked @endif" for="restriccion_institucional">
                            <input type="radio" id="restriccion_institucional" name="restriccion_red" value="1" @if($isRestringido) checked @endif onchange="toggleIpOptions(true)">
                            <div class="selection-card-content">
                                <div class="selection-card-title"><i class="fa fa-university text-warning me-1"></i> Exclusivo Red Institucional</div>
                                <div class="selection-card-subtitle">Limita el acceso únicamente a equipos conectados dentro de la red del campus / laboratorio.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-3 @if(!$isRestringido) d-none @endif" id="container_ips_autorizadas">
                    <label for="ips_autorizadas" class="form-control-label font-weight-bold text-xs">Rangos de IP o Subredes Autorizadas Personalizadas (Opcional)</label>
                    <input class="form-control form-control-sm" type="text" id="ips_autorizadas" name="ips_autorizadas" placeholder="Ej: 192.168.1.0/24, 10.0.5.12 (Dejar en blanco para usar la red por defecto del campus)"
                        value="{{ isset($certamen) ? old('ips_autorizadas', $certamen->ips_autorizadas) : old('ips_autorizadas') }}">
                    <span class="text-xxs text-muted">Separar múltiples direcciones IP o notación CIDR por comas.</span>
                </div>

                <div class="mt-3 pt-3 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-12">
                            <label for="certamen_password" class="form-control-label font-weight-bold text-xs mb-1">
                                <i class="fa fa-key text-warning me-1"></i> Contraseña de Acceso a la Evaluación (Opcional)
                            </label>
                            <p class="text-xxs text-secondary mb-0">Si establece una contraseña, los estudiantes deberán ingresarla obligatoriamente para poder iniciar la evaluación.</p>
                        </div>
                        <div class="col-md-5 col-12 mt-2 mt-md-0">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm" type="password" id="certamen_password" name="password" placeholder="Dejar en blanco para acceso libre"
                                    value="{{ isset($certamen) ? old('password', $certamen->password) : old('password') }}" autocomplete="new-password">
                                <button class="btn btn-outline-secondary mb-0" type="button" onclick="togglePasswordVisibility('certamen_password', this)" title="Mostrar / Ocultar Contraseña">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-danger text-xxs pt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dificultad de la Evaluación -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <h6 class="mb-1 text-sm font-weight-bold text-dark"><i class="fa fa-layer-group text-primary me-2"></i>Nivel de Dificultad de la Evaluación</h6>
                <p class="text-xs text-secondary mb-3">Establezca la complejidad global recomendada para esta evaluación.</p>
                
                @php
                    $difActual = isset($certamen) ? old('dificultad', $certamen->dificultad ?? 'Medio') : old('dificultad', 'Medio');
                @endphp
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="selection-card w-100 mb-0 @if($difActual == 'Fácil') checked @endif" for="dif_facil">
                            <input type="radio" id="dif_facil" name="dificultad" value="Fácil" @if($difActual == 'Fácil') checked @endif>
                            <div class="selection-card-content">
                                <div class="selection-card-title"><span class="badge border border-success text-success bg-white px-2 py-1 me-1"><i class="fa fa-circle me-1 text-success" style="font-size: 7px; vertical-align: middle;"></i>Fácil</span> Principiante</div>
                                <div class="selection-card-subtitle">Ejercicios introductorios y conceptos básicos.</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="selection-card w-100 mb-0 @if($difActual == 'Medio') checked @endif" for="dif_medio">
                            <input type="radio" id="dif_medio" name="dificultad" value="Medio" @if($difActual == 'Medio') checked @endif>
                            <div class="selection-card-content">
                                <div class="selection-card-title"><span class="badge border border-warning text-warning bg-white px-2 py-1 me-1"><i class="fa fa-circle me-1 text-warning" style="font-size: 7px; vertical-align: middle;"></i>Medio</span> Intermedio</div>
                                <div class="selection-card-subtitle">Problemas de lógica estándar y algoritmos.</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="selection-card w-100 mb-0 @if($difActual == 'Difícil') checked @endif" for="dif_dificil">
                            <input type="radio" id="dif_dificil" name="dificultad" value="Difícil" @if($difActual == 'Difícil') checked @endif>
                            <div class="selection-card-content">
                                <div class="selection-card-title"><span class="badge border border-danger text-danger bg-white px-2 py-1 me-1"><i class="fa fa-circle me-1 text-danger" style="font-size: 7px; vertical-align: middle;"></i>Difícil</span> Avanzado</div>
                                <div class="selection-card-subtitle">Desafíos avanzados de optimización y lógica.</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuración de Preguntas y Bancos de Preguntas Aleatorios -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold text-dark">
                            <i class="fa fa-cubes text-primary me-2"></i>Estructura de la Evaluación y Bancos de Preguntas
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                            Indique cuántas preguntas tendrá la evaluación. Para cada pregunta, configure su banco (categoría o variantes) desde el cual el sistema asignará <strong>1 ejercicio al azar</strong> para cada estudiante.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-xs font-weight-bold text-secondary me-1">Cantidad de Preguntas:</span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <button class="btn btn-outline-secondary mb-0 px-2.5" type="button" onclick="modificarCantidadPreguntas(-1)"><i class="fa fa-minus"></i></button>
                            <input type="number" id="input_cant_preguntas" name="cantidad_problemas" class="form-control text-center font-weight-bold" min="1" max="25" value="{{ count($bancos_preguntas ?? [1]) }}" onchange="onManualCantidadChange(this.value)">
                            <button class="btn btn-outline-secondary mb-0 px-2.5" type="button" onclick="modificarCantidadPreguntas(1)"><i class="fa fa-plus"></i></button>
                        </div>
                        <button type="button" class="btn btn-sm btn-dark mb-0 ms-1" onclick="agregarNuevaPregunta()">
                            <i class="fa fa-plus me-1"></i> Agregar Pregunta
                        </button>
                    </div>
                </div>

                <input type="hidden" name="bancos_preguntas_json" id="bancos_preguntas_json" value="">

                <!-- Contenedor dinámico de Preguntas y sus Bancos -->
                <div id="preguntas-dinamicas-container" class="d-flex flex-column gap-3">
                    <!-- Se renderiza vía JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $todos_problemas_js = isset($todos_problemas) ? $todos_problemas->map(function($p) {
        return [
            'id' => (int)$p->id,
            'nombre' => $p->nombre,
            'codigo' => $p->codigo,
            'dificultad' => $p->dificultad ?? 'Medio',
            'categoria_ids' => $p->categorias ? $p->categorias->pluck('id')->map(fn($id)=>(int)$id)->toArray() : [],
            'categoria_nombres' => $p->categorias ? $p->categorias->pluck('nombre')->toArray() : [],
        ];
    })->values() : collect();

    $categorias_js = isset($categorias_existentes) ? $categorias_existentes->map(function($c) {
        return [
            'id' => (int)$c->id,
            'nombre' => $c->nombre,
        ];
    })->values() : collect();

    $bancos_init = isset($bancos_preguntas) && !empty($bancos_preguntas) ? $bancos_preguntas : [
        [
            'numero' => 1,
            'id_categoria' => null,
            'nombre_banco' => 'Pregunta 1',
            'problemas' => []
        ]
    ];
@endphp

<script>
    const todosProblemasData = @json($todos_problemas_js);
    const categoriasData = @json($categorias_js);
    let bancosData = @json($bancos_init);

    if (!Array.isArray(bancosData) || bancosData.length === 0) {
        bancosData = [{ numero: 1, id_categoria: null, nombre_banco: 'Pregunta 1', problemas: [] }];
    }

    function toggleIpOptions(show) {
        var container = document.getElementById('container_ips_autorizadas');
        var cardLibre = document.getElementById('restriccion_libre').closest('.selection-card');
        var cardInst = document.getElementById('restriccion_institucional').closest('.selection-card');
        if (show) {
            container.classList.remove('d-none');
            cardLibre.classList.remove('checked');
            cardInst.classList.add('checked');
        } else {
            container.classList.add('d-none');
            cardInst.classList.remove('checked');
            cardLibre.classList.add('checked');
        }
    }

    function togglePasswordVisibility(inputId, btn) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    function syncBancosJson() {
        var hiddenInput = document.getElementById('bancos_preguntas_json');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(bancosData);
        }
        var inputCant = document.getElementById('input_cant_preguntas');
        if (inputCant) {
            inputCant.value = bancosData.length;
        }
    }

    function getNombreCategoria(catId) {
        var cat = categoriasData.find(function(c) { return c.id == catId; });
        return cat ? cat.nombre : 'Categoría #' + catId;
    }

    function renderPreguntas() {
        var container = document.getElementById('preguntas-dinamicas-container');
        if (!container) return;
        container.innerHTML = '';

        bancosData.forEach(function(banco, idx) {
            var num = idx + 1;
            banco.numero = num;
            var probCount = Array.isArray(banco.problemas) ? banco.problemas.length : 0;
            var tituloBanco = banco.nombre_banco || (banco.id_categoria ? getNombreCategoria(banco.id_categoria) : 'Sin banco asignado');
            var badgeClase = probCount > 0 ? 'bg-success' : 'bg-warning text-dark';
            var disabledEliminar = bancosData.length <= 1 ? 'disabled style="opacity: 0.5;"' : '';

            // Opciones del select de categorías
            var optionsCats = '';
            categoriasData.forEach(function(cat) {
                var selected = (banco.id_categoria == cat.id) ? 'selected' : '';
                var countEnCat = todosProblemasData.filter(function(p) {
                    return p.categoria_ids.indexOf(cat.id) !== -1;
                }).length;
                optionsCats += `<option value="${cat.id}" ${selected}>${cat.nombre} (${countEnCat} disponibles)</option>`;
            });

            var isCustom = (banco.id_categoria === null && probCount > 0);

            // Generar cards de problemas para el drawer de esta pregunta
            var cardsProbsHtml = '';
            todosProblemasData.forEach(function(p) {
                var isChecked = banco.problemas && banco.problemas.indexOf(p.id) !== -1;
                var difClass = p.dificultad === 'Fácil' ? 'border-success text-success' : (p.dificultad === 'Difícil' ? 'border-danger text-danger' : 'border-warning text-warning');
                var catBadges = p.categoria_nombres.map(function(cn) {
                    return `<span class="badge border border-secondary text-secondary bg-white text-xxs px-1 py-0.5 me-1">${cn}</span>`;
                }).join('');

                cardsProbsHtml += `
                    <div class="col-md-6 col-12 item-problema-drawer" data-id="${p.id}" data-nombre="${p.nombre.toLowerCase()}" data-codigo="${p.codigo.toLowerCase()}">
                        <label class="selection-card w-100 mb-0 py-2 px-2.5 ${isChecked ? 'checked' : ''}" for="prob_q${idx}_${p.id}">
                            <input type="checkbox" id="prob_q${idx}_${p.id}" value="${p.id}" ${isChecked ? 'checked' : ''} onchange="onProblemaToggle(${idx}, ${p.id}, this)">
                            <div class="selection-card-content">
                                <div class="d-flex justify-content-between align-items-center mb-0.5">
                                    <span class="selection-card-title text-xs mb-0">${p.nombre}</span>
                                    <span class="badge border ${difClass} bg-white text-xxs ms-1">${p.dificultad}</span>
                                </div>
                                <div class="selection-card-subtitle d-flex flex-wrap align-items-center gap-1 mt-0">
                                    <span class="text-xxs text-muted">Código: <span class="badge bg-secondary text-xxs px-1.5 py-0.2">${p.codigo}</span></span>
                                    ${catBadges}
                                </div>
                            </div>
                        </label>
                    </div>
                `;
            });

            var cardHtml = `
                <div class="card border shadow-xs pregunta-card" id="card_pregunta_${idx}">
                    <div class="card-header bg-gray-100 py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-xs px-2.5 py-1">Pregunta #${num}</span>
                            <span class="text-xs font-weight-bold text-dark banco-titulo-label" id="label_titulo_${idx}">
                                <i class="fa fa-layer-group text-danger me-1"></i> Banco: ${tituloBanco}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${badgeClase} text-xxs px-2 py-1 banco-contador-badge" id="badge_count_${idx}">
                                ${probCount} Variante(s) en Banco
                            </span>
                            <button type="button" class="btn btn-xs btn-outline-primary mb-0 py-1 px-2" onclick="toggleDrawerPregunta(${idx})" title="Ver o ajustar las variantes exactas del banco">
                                <i class="fa fa-sliders-h me-1"></i> Configurar Variantes
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger mb-0 py-1 px-2" onclick="eliminarPregunta(${idx})" title="Eliminar pregunta" ${disabledEliminar}>
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7 col-12">
                                <label class="form-control-label text-xs font-weight-bold mb-1">
                                    Asignar Banco / Categoría de Origen:
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fa fa-folder-open text-warning"></i></span>
                                    <select class="form-select form-select-sm select-categoria-banco" id="select_cat_${idx}" onchange="onCategoriaChange(${idx}, this.value)">
                                        <option value="">-- Seleccionar Banco (Categoría) --</option>
                                        ${optionsCats}
                                        <option value="custom" ${isCustom ? 'selected' : ''}>-- Selección Personalizada / Múltiples Categorías --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5 col-12 text-md-end text-start mt-2 mt-md-0">
                                <span class="text-xxs text-secondary d-block">
                                    <i class="fa fa-random text-info me-1"></i> Cada estudiante recibirá <strong>1 ejercicio al azar</strong> de este banco.
                                </span>
                            </div>
                        </div>

                        <!-- Drawer colapsable para ver/ajustar variantes -->
                        <div class="drawer-ejercicios mt-3 pt-3 border-top" id="drawer_${idx}" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-xs font-weight-bold text-dark">Variantes disponibles para esta pregunta:</span>
                                    <span class="text-xxs text-muted">(Marque los ejercicios que entran al sorteo)</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xxs btn-outline-secondary mb-0 py-0.5 px-2" onclick="marcarTodosPregunta(${idx}, true)">
                                        Marcar Todos
                                    </button>
                                    <button type="button" class="btn btn-xxs btn-outline-secondary mb-0 py-0.5 px-2" onclick="marcarTodosPregunta(${idx}, false)">
                                        Desmarcar Todos
                                    </button>
                                </div>
                            </div>

                            <div class="input-group input-group-sm mb-2" style="max-width: 320px;">
                                <span class="input-group-text"><i class="fa fa-search text-xs"></i></span>
                                <input type="text" class="form-control form-control-sm" placeholder="Buscar por nombre o código..." onkeyup="filtrarProblemasDrawer(${idx}, this.value)">
                            </div>

                            <div class="row g-2 contenedor-lista-problemas" id="lista_probs_${idx}" style="max-height: 240px; overflow-y: auto;">
                                ${cardsProbsHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });

        syncBancosJson();
    }

    function toggleDrawerPregunta(idx) {
        var drawer = document.getElementById('drawer_' + idx);
        if (drawer) {
            if (drawer.style.display === 'none' || drawer.style.display === '') {
                drawer.style.display = 'block';
            } else {
                drawer.style.display = 'none';
            }
        }
    }

    function onCategoriaChange(idx, val) {
        var banco = bancosData[idx];
        if (!banco) return;

        if (!val || val === '') {
            banco.id_categoria = null;
            banco.nombre_banco = 'Sin banco asignado';
            banco.problemas = [];
        } else if (val === 'custom') {
            banco.id_categoria = null;
            banco.nombre_banco = 'Selección Personalizada';
            var drawer = document.getElementById('drawer_' + idx);
            if (drawer) drawer.style.display = 'block';
        } else {
            var catId = parseInt(val);
            banco.id_categoria = catId;
            banco.nombre_banco = getNombreCategoria(catId);
            var probIds = [];
            todosProblemasData.forEach(function(p) {
                if (p.categoria_ids.indexOf(catId) !== -1) {
                    probIds.push(p.id);
                }
            });
            banco.problemas = probIds;
        }

        actualizarCardPregunta(idx);
        syncBancosJson();
    }

    function onProblemaToggle(preguntaIdx, probId, checkbox) {
        var banco = bancosData[preguntaIdx];
        if (!banco) return;
        if (!Array.isArray(banco.problemas)) banco.problemas = [];

        var pId = parseInt(probId);
        var card = checkbox.closest('.selection-card');
        if (checkbox.checked) {
            if (banco.problemas.indexOf(pId) === -1) {
                banco.problemas.push(pId);
            }
            if (card) card.classList.add('checked');
        } else {
            banco.problemas = banco.problemas.filter(function(id) { return id !== pId; });
            if (card) card.classList.remove('checked');
        }

        actualizarCardPregunta(preguntaIdx);
        syncBancosJson();
    }

    function marcarTodosPregunta(preguntaIdx, marcar) {
        var banco = bancosData[preguntaIdx];
        if (!banco) return;

        var drawer = document.getElementById('drawer_' + preguntaIdx);
        if (!drawer) return;

        if (marcar) {
            var allIds = [];
            todosProblemasData.forEach(function(p) { allIds.push(p.id); });
            banco.problemas = allIds;
        } else {
            banco.problemas = [];
        }

        actualizarCardPregunta(preguntaIdx);
        syncBancosJson();
    }

    function filtrarProblemasDrawer(idx, query) {
        var q = (query || '').toLowerCase().trim();
        var drawer = document.getElementById('drawer_' + idx);
        if (!drawer) return;

        var items = drawer.querySelectorAll('.item-problema-drawer');
        items.forEach(function(item) {
            var nombre = item.getAttribute('data-nombre') || '';
            var codigo = item.getAttribute('data-codigo') || '';
            if (nombre.indexOf(q) !== -1 || codigo.indexOf(q) !== -1) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    function actualizarCardPregunta(idx) {
        var banco = bancosData[idx];
        if (!banco) return;

        var probCount = Array.isArray(banco.problemas) ? banco.problemas.length : 0;
        var labelTitulo = document.getElementById('label_titulo_' + idx);
        if (labelTitulo) {
            var titulo = banco.nombre_banco || (banco.id_categoria ? getNombreCategoria(banco.id_categoria) : 'Sin banco asignado');
            labelTitulo.innerHTML = `<i class="fa fa-layer-group text-danger me-1"></i> Banco: ${titulo}`;
        }

        var badgeCount = document.getElementById('badge_count_' + idx);
        if (badgeCount) {
            badgeCount.className = `badge ${probCount > 0 ? 'bg-success' : 'bg-warning text-dark'} text-xxs px-2 py-1 banco-contador-badge`;
            badgeCount.innerText = `${probCount} Variante(s) en Banco`;
        }

        var drawer = document.getElementById('drawer_' + idx);
        if (drawer) {
            var checkboxes = drawer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(cb) {
                var pId = parseInt(cb.value);
                var isChecked = banco.problemas.indexOf(pId) !== -1;
                cb.checked = isChecked;
                var card = cb.closest('.selection-card');
                if (card) {
                    if (isChecked) card.classList.add('checked');
                    else card.classList.remove('checked');
                }
            });
        }
    }

    function modificarCantidadPreguntas(delta) {
        var nuevaCant = bancosData.length + delta;
        if (nuevaCant < 1) nuevaCant = 1;
        if (nuevaCant > 25) nuevaCant = 25;
        setCantidadPreguntas(nuevaCant);
    }

    function onManualCantidadChange(val) {
        var num = parseInt(val);
        if (isNaN(num) || num < 1) num = 1;
        if (num > 25) num = 25;
        setCantidadPreguntas(num);
    }

    function setCantidadPreguntas(targetCount) {
        while (bancosData.length < targetCount) {
            bancosData.push({
                numero: bancosData.length + 1,
                id_categoria: null,
                nombre_banco: 'Pregunta ' + (bancosData.length + 1),
                problemas: []
            });
        }
        while (bancosData.length > targetCount) {
            bancosData.pop();
        }
        renderPreguntas();
    }

    function agregarNuevaPregunta() {
        bancosData.push({
            numero: bancosData.length + 1,
            id_categoria: null,
            nombre_banco: 'Pregunta ' + (bancosData.length + 1),
            problemas: []
        });
        renderPreguntas();
    }

    function eliminarPregunta(idx) {
        if (bancosData.length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Acción no permitida',
                    text: 'La evaluación debe tener al menos una pregunta.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('La evaluación debe tener al menos una pregunta.');
            }
            return;
        }

        bancosData.splice(idx, 1);
        renderPreguntas();
    }

    function validarBancosAntesDeEnviar() {
        syncBancosJson();

        if (bancosData.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Evaluación incompleta',
                    text: 'Debe configurar al menos una pregunta para la evaluación.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('Debe configurar al menos una pregunta para la evaluación.');
            }
            return false;
        }

        for (var i = 0; i < bancosData.length; i++) {
            var b = bancosData[i];
            if (!b.problemas || b.problemas.length === 0) {
                var numPreg = i + 1;
                var drawer = document.getElementById('drawer_' + i);
                if (drawer) drawer.style.display = 'block';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pregunta sin banco asignado',
                        text: `La Pregunta #${numPreg} no tiene ningún ejercicio asignado en su banco. Seleccione una categoría o marque al menos un ejercicio.`,
                        confirmButtonText: 'Revisar Pregunta'
                    });
                } else {
                    alert(`La Pregunta #${numPreg} no tiene ningún ejercicio asignado en su banco.`);
                }
                return false;
            }
        }
        return true;
    }

    // Interceptar submitFormCrear y submitFormEditar para validar antes del modal
    if (typeof window.submitFormCrear === 'function') {
        const origSubmitCrear = window.submitFormCrear;
        window.submitFormCrear = function() {
            if (!validarBancosAntesDeEnviar()) return;
            origSubmitCrear();
        };
    }

    if (typeof window.submitFormEditar === 'function') {
        const origSubmitEditar = window.submitFormEditar;
        window.submitFormEditar = function(item) {
            if (!validarBancosAntesDeEnviar()) return;
            origSubmitEditar(item);
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderPreguntas();

        // En caso de submit directo
        var formCrear = document.getElementById('crearForm');
        if (formCrear) {
            formCrear.addEventListener('submit', function(e) {
                if (!validarBancosAntesDeEnviar()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
        }

        var formEditar = document.getElementById('editarForm');
        if (formEditar) {
            formEditar.addEventListener('submit', function(e) {
                if (!validarBancosAntesDeEnviar()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
        }
    });
</script>