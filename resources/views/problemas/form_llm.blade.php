<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom mt-4">
    <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-robot text-info me-2"></i>Configuración de la Inteligencia Artificial (LLM)</h6>
</div>
<div class="row d-flex justify-content-start align-items-center">
    <div class="col">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="habilitar_llm" name="habilitar_llm" value="{{true}}" @if(isset($problema) && $problema->habilitar_llm==true)checked @elseif(old('habilitar_llm')) checked @endif>
            <label class="form-check-label" for="habilitar_llm">Habilitar Large Language Model</label>
        </div>
    </div>
    <div class="col">
        <div class="form-group has-danger">
            <label for="limite_llm" class="form-control-label">Límite de uso de LLM</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control @error('limite_llm') is-invalid @enderror"
                    name="limite_llm" id="limite_llm" placeholder="Ej. 3" min="0" value="{{isset($problema)? old('limite_llm', $problema->limite_llm):old('limite_llm')}}">
                <span class="input-group-text" id="basic-addon1">Usos</span>
            </div>
            @error('limite_llm')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="body_problema_resumido">Enunciado del Problema Resumido</label>
            <p class="opacity-25"><small>Esté es un resumen del problema que se utilizara en la Large Languge Model
                    para la retroalimentación.</small></p>
            <textarea class="form-control @error('body_problema_resumido') is-invalid @enderror" id="body_problema_resumido"
                name="body_problema_resumido" rows="4" placeholder="Ej. El código debe entregar una suma de dos numeros">{{ isset($problema) ? old('body_problema_resumido', $problema->body_problema_resumido) : old('body_problema_resumido') }}</textarea>
        </div>
        @error('body_problema_resumido')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>
<div class="row mt-3">
    <div class="col">
        <div class="form-group">
            <label for="restricciones" class="form-control-label font-weight-bold text-dark"><i class="fa fa-exclamation-triangle text-warning me-1"></i>Restricciones (Opcional)</label>
            <p class="opacity-75 text-xs text-secondary mb-1">Especifique las reglas o restricciones de código impuestas por el profesor (Ej. "Utilizar únicamente ciclo 'for'", "Prohibido usar librería math", "Usar while", etc.). El bot integrado verificará si el alumno cumple o no cumple.</p>
            <textarea class="form-control @error('restricciones') is-invalid @enderror" id="restricciones"
                name="restricciones" rows="3" placeholder="Ej. El código debe utilizar únicamente ciclo for y no debe usar la estructura while.">{{ isset($problema) ? old('restricciones', $problema->restricciones) : old('restricciones') }}</textarea>
        </div>
        @error('restricciones')
            <p class="text-danger text-xs pt-1"> {{ $message }} </p>
        @enderror
    </div>
</div>