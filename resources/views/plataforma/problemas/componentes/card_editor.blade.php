<div class="card border-danger" style="height:100%">
    <div class="card-header">
        Editor de Código
    </div>
    <div class="card-body">
        @error('codigo')
            <p class="text-danger text-xs pt-1"> Debes escribir al menos una línea de código para enviarlo como
                solución.</p>
        @enderror
        <div id="editor">{{ isset($last_envio->codigo) ? old('codigo', $last_envio->codigo) : '' }}</div>
    </div>
</div>