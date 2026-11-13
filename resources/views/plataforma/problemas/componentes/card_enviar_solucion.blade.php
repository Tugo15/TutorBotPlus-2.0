<div class="card border-danger" style="{{ isset($res_certamen) ? 'min-height: 35rem; height: 100%;' : 'height: 100%;' }}">
    <div class="card-body p-3 px-sm-4">
        <form
            action="{{ isset($res_certamen) ? route('certamenes.guardar_codigo') : route('problemas.guardar_codigo') }}"
            method="POST" id="guardarForm">
            @csrf
            <input type="hidden" id="codigo_save" name="codigo_save">
            <input type="hidden" id="lenguaje_save" name="lenguaje_save">
            <input type="hidden" name="id_problema" value="{{ $problema->id }}">
            <input type="hidden" name="codigo_problema" value="{{ $problema->codigo }}">
            <input type="hidden" name="id_curso" value="{{ $id_curso ?? ($res_certamen && $res_certamen->certamen ? $res_certamen->certamen->id_curso : '') }}">
            <input type="hidden" name="id_resolver" value="{{ $last_envio->id_resolver ?? '' }}">
            <input type="hidden" name="id_cursa" value="{{ $last_envio->id_cursa ?? '' }}">
            <input type="hidden" name="id_envio" value="{{ $last_envio->id ?? '' }}">
            @if(isset($res_certamen))
                <input type="hidden" name="id_certamen" value="{{ $res_certamen->id }}">
                <input type="hidden" name="token_certamen" value="{{ $res_certamen->token }}">
            @endif
            <div class="d-grid mb-2">
                <button class="btn btn-outline-primary btn-sm text-wrap" type="submit" id="boton_guardar">
                    <i class="fa fa-save me-1"></i> {{ isset($res_certamen) ? "Guardar y Volver a la Evaluación" : "Guardar y Volver" }}
                </button>
            </div>
        </form>
        <form
            action="{{ isset($res_certamen) ? route('problemas.enviar', ['id_problema' => $problema->id, 'id_resolver' => $last_envio->id_resolver, 'id_certamen' => $res_certamen->id, 'token_certamen' => $res_certamen->token]) : route('problemas.enviar', ['id_problema' => $problema->id, 'id_resolver' => $last_envio->id_resolver, 'id_cursa' => $last_envio->id_cursa]) }}"
            method="POST" id="evaluacion_form">
            @csrf
            <div class="d-grid mb-3">
                <button class="btn btn-primary text-wrap" type="submit" id="boton_enviar_solucion">
                    <i class="fa fa-paper-plane me-1"></i> Enviar Solución
                </button>
            </div>
            <h6 class="text-center mt-3 font-weight-bold text-xs text-uppercase text-secondary">Lenguaje de Programación</h6>
            <select class="form-select mb-3 text-xs" id="lenguaje" name="lenguaje"
                onchange="change_language(this)" required>
                    <option value="" disabled>Selecciona un Lenguaje</option>
                @foreach ($lenguajes as $item)
                    <option value="{{ $item->codigo }}" id="{{$item->id}}" @if($last_envio->ProblemaLenguaje->lenguaje->id == $item->id) selected @endif>
                        {{ $item->nombre }}</option>
                @endforeach
            </select>
            <h6 class="text-center mt-2 font-weight-bold text-xs text-uppercase text-secondary">Evaluador</h6>
            <select class="form-select mb-3 text-xs" id="juez_virtual" name="juez_virtual">
                <option value="0">Selección Aleatoria de Juez Virtual</option>
                @foreach ($jueces as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
            <input type="hidden" id="codigo" name="codigo" value="">
        </form>
    </div>
</div>