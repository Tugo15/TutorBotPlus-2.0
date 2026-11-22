<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
    <div>
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-graduation-cap me-2 text-success"></i>Información del Curso</h6>
        <p class="text-xs text-secondary mb-0">Complete o actualice los datos básicos del curso en el sistema.</p>
    </div>
    <span class="text-xs text-danger font-weight-bold">* Campo Obligatorio</span>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="nombre" class="form-control-label font-weight-bold text-sm mb-0 @error('nombre') is-invalid @enderror">Nombre del Curso*</label>
                <small class="text-xs text-secondary" id="counter_nombre">0 / 100</small>
            </div>
            <input class="form-control" type="text" id="nombre" name="nombre" maxlength="100" required
                placeholder="Ej. Taller de Programación Python"
                value="{{ isset($curso) ? old('nombre', $curso->nombre) : old('nombre') }}">
            @error('nombre')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-danger mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="codigo" class="form-control-label font-weight-bold text-sm mb-0 @error('codigo') is-invalid @enderror">Código del Curso*</label>
                <small class="text-xs text-secondary" id="counter_codigo">0 / 25</small>
            </div>
            <input class="form-control" type="text" id="codigo" name="codigo" maxlength="25" required
                placeholder="Ej. INF-101"
                value="{{ isset($curso) ? old('codigo', $curso->codigo) : old('codigo') }}">
            @error('codigo')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-12">
        <div class="form-group has-danger mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="descripcion" class="form-control-label font-weight-bold text-sm mb-0">Descripción del Curso</label>
                <small class="text-xs text-secondary" id="counter_descripcion">0 / 60</small>
            </div>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                rows="3" maxlength="60" placeholder="Introduzca una descripción breve del curso (máx. 60 caracteres)">{{ isset($curso) ? old('descripcion', $curso->descripcion) : old('descripcion') }}</textarea>
            <small class="text-xs text-muted d-block mt-1">Máximo 60 caracteres para que las tarjetas de cursos se mantengan en una línea y sin recortes.</small>
            @error('descripcion')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<script>
    (function () {
        function bindCharCounter(inputId, counterId, maxLen) {
            var input = document.getElementById(inputId);
            var counter = document.getElementById(counterId);
            if (!input || !counter) return;

            function update() {
                var len = (input.value || '').length;
                counter.textContent = len + ' / ' + maxLen;
                counter.classList.remove('text-secondary', 'text-warning', 'text-danger', 'font-weight-bold');
                if (len >= maxLen) {
                    counter.classList.add('text-danger', 'font-weight-bold');
                } else if (len >= Math.floor(maxLen * 0.85)) {
                    counter.classList.add('text-warning', 'font-weight-bold');
                } else {
                    counter.classList.add('text-secondary');
                }
            }

            input.addEventListener('input', update);
            update();
        }

        document.addEventListener('DOMContentLoaded', function () {
            bindCharCounter('nombre', 'counter_nombre', 100);
            bindCharCounter('codigo', 'counter_codigo', 25);
            bindCharCounter('descripcion', 'counter_descripcion', 60);
        });
    })();
</script>
