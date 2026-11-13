<div class="card border-danger" style="height:100%">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Editor de Código</span>
        <span id="autosave-status" class="badge text-bg-light border text-secondary fw-normal" style="font-size: 0.72rem; display: none;">
            <i class="fa fa-check-circle text-success me-1"></i>Borrador guardado
        </span>
    </div>
    <div class="card-body">
        @if(isset($problema->restricciones) && trim($problema->restricciones) !== '')
            <div class="alert alert-warning border-warning d-flex align-items-center mb-3" role="alert">
                <i class="fa fa-exclamation-triangle text-warning me-2"></i>
                <div>
                    <strong>Restricción:</strong> {{ $problema->restricciones }}
                </div>
            </div>
        @endif
        @error('codigo')
            <p class="text-danger text-xs pt-1"> Debes escribir al menos una línea de código para enviarlo como
                solución.</p>
        @enderror
        <div id="editor">{{ isset($last_envio->codigo) ? old('codigo', $last_envio->codigo) : '' }}</div>
    </div>
</div>