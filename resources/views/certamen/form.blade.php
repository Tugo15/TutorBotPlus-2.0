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
        <div class="form-group">
            <label for="descripcion" class="form-control-label font-weight-bold text-sm">Descripción del Certamen*</label>
            <input type="hidden" id="descripcion" name="descripcion"
                value="{{ isset($certamen) ? old('descripcion', $certamen->descripcion) : old('descripcion') }}">
            <div class="flex flex-col space-y-2">
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
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

<!-- Selector Interactivo de Problemas -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border shadow-xs mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold text-dark"><i class="fa fa-puzzle-piece text-primary me-2"></i>Asignar Problemas a la Evaluación</h6>
                        <p class="text-xs text-secondary mb-0">Seleccione directamente los problemas que formarán parte de esta evaluación:</p>
                    </div>
                    <div>
                        <span class="badge bg-info text-xs px-2 py-1" id="selected_count_badge">0 Problemas Seleccionados</span>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" id="search_problemas" class="form-control" placeholder="Buscar problema por nombre o código..." onkeyup="filterProblems()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-tags text-danger"></i></span>
                            <select id="filter_categoria" class="form-select" onchange="filterProblems()">
                                <option value="Todas">Todas las Categorías</option>
                                @php
                                    $cats_select = isset($categorias_existentes) ? $categorias_existentes : \App\Models\Categoria_Problema::orderBy('nombre')->get();
                                @endphp
                                @foreach($cats_select as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @php
                    $selectedProbArray = isset($problemas_seleccionados) ? $problemas_seleccionados : old('problemas', []);
                @endphp
                
                <div class="row g-2" id="problemas-container" style="max-height: 380px; overflow-y: auto;">
                    @if(isset($todos_problemas) && count($todos_problemas) > 0)
                        @foreach($todos_problemas as $prob)
                            @php
                                $isProbChecked = in_array($prob->id, $selectedProbArray);
                                $difBadgeClass = $prob->dificultad == 'Fácil' ? 'border border-success text-success bg-white' : ($prob->dificultad == 'Difícil' ? 'border border-danger text-danger bg-white' : 'border border-warning text-warning bg-white');
                                $probCats = $prob->categorias;
                                $catIdsStr = $probCats ? implode(',', $probCats->pluck('id')->toArray()) : '';
                            @endphp
                            <div class="col-md-6 problem-item-card" data-nombre="{{ strtolower($prob->nombre) }}" data-codigo="{{ strtolower($prob->codigo) }}" data-categorias="{{ $catIdsStr }}">
                                <label class="selection-card w-100 mb-0 @if($isProbChecked) checked @endif" for="prob_{{ $prob->id }}">
                                    <input type="checkbox" id="prob_{{ $prob->id }}" name="problemas[]" value="{{ $prob->id }}" @if($isProbChecked) checked @endif onchange="onProblemToggle(this)">
                                    <div class="selection-card-content">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="selection-card-title mb-0">{{ $prob->nombre }}</div>
                                            <span class="badge {{ $difBadgeClass }} text-xxs ms-2">{{ $prob->dificultad ?? 'Medio' }}</span>
                                        </div>
                                        <div class="selection-card-subtitle d-flex flex-wrap align-items-center gap-1">
                                            <span>Código: <span class="badge bg-secondary text-xxs px-2 py-0.5">{{ $prob->codigo }}</span></span>
                                            @if($probCats && $probCats->isNotEmpty())
                                                @foreach($probCats as $c)
                                                    <span class="badge border border-danger text-danger bg-white text-xxs px-1.5 py-0.5">{{ $c->nombre }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center py-4">
                            <p class="text-secondary text-sm mb-0">No se encontraron problemas disponibles en la plataforma.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

    function onProblemToggle(checkbox) {
        var card = checkbox.closest('.selection-card');
        if (card) {
            card.classList.toggle('checked', checkbox.checked);
        }
        updateSelectedBadge();
    }

    function updateSelectedBadge() {
        var container = document.getElementById('problemas-container');
        if (!container) return;
        var checkedCount = container.querySelectorAll('input[type="checkbox"]:checked').length;
        var badge = document.getElementById('selected_count_badge');
        if (badge) {
            badge.innerText = checkedCount + ' Problema(s) Seleccionado(s)';
        }
    }

    function filterProblems() {
        var query = document.getElementById('search_problemas').value.toLowerCase().trim();
        var catSelect = document.getElementById('filter_categoria');
        var selectedCat = catSelect ? catSelect.value : 'Todas';
        var cards = document.querySelectorAll('.problem-item-card');

        cards.forEach(function(card) {
            var nombre = card.getAttribute('data-nombre') || '';
            var codigo = card.getAttribute('data-codigo') || '';
            var catsStr = card.getAttribute('data-categorias') || '';
            var catArray = catsStr.split(',').filter(Boolean);

            var matchesSearch = (nombre.indexOf(query) !== -1 || codigo.indexOf(query) !== -1);
            var matchesCat = (selectedCat === 'Todas' || catArray.indexOf(selectedCat) !== -1);

            if (matchesSearch && matchesCat) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedBadge();
    });
</script>