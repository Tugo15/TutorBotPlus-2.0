<div class="card border-danger" style="{{isset($res_certamen)? 'height:35rem;' : 'height:100%;'}}">
    <div class="card-body px-5">
        <form
            action="{{isset($res_certamen)?  route('certamenes.guardar_codigo', ['id_certamen'=>$res_certamen, 'token_certamen'=>$res_certamen->token]) : route('problemas.guardar_codigo', ['codigo_problema' => $problema->codigo, 'id_problema' => $problema->id,'id_curso' => $id_curso, 'id_resolver'=>$last_envio->id_resolver, 'id_cursa'=>$last_envio->id_cursa])}}"
            method="POST" id="guardarForm">
            @csrf
            <div class="row px-5 mb-2">

                <input type="hidden" id="codigo_save" name="codigo_save">
                <input type="hidden" id="lenguaje_save" name="lenguaje_save">
                <button class="btn btn-outline-primary btn-sm" type="submit" id="boton_guardar">{{isset($res_certamen)? "Guardar y Volver a la Evaluación" : "Guardar y Volver"}}</button>
            </div>
        </form>
        <form
            action="{{isset($res_certamen)? route('problemas.enviar', ['id_problema' => $problema->id,'id_resolver'=>$last_envio->id_resolver, 'id_certamen'=>$res_certamen->id, 'token_certamen'=>$res_certamen->token]) : route('problemas.enviar', ['id_problema' => $problema->id, 'id_resolver'=>$last_envio->id_resolver, 'id_cursa'=>$last_envio->id_cursa]) }}"
            method="POST" id="evaluacion_form">
            @csrf
            <div class="row px-5">
                <button class="btn btn-primary" type="submit" id="boton_enviar_solucion">Enviar Solución</button>
            </div>
            <h6 class="text-center mt-4">Lenguaje de Programación</h6>
            <select class="form-select mb-3" id="lenguaje" name="lenguaje"
                onchange="change_language(this)" required>
                    <option value="">Selecciona un Lenguaje</option>
                @foreach ($lenguajes as $item)
                    <option value="{{ $item->codigo }}" id="{{$item->id}}" @if($last_envio->ProblemaLenguaje->lenguaje->id == $item->id) selected @endif>
                        {{ $item->nombre }}</option>
                @endforeach
            </select>
            <h6 class="text-center mt-2">Evaluador</h6>
            <select class="form-select mb-3" id="juez_virtual" name="juez_virtual">
                <option value="0">Selección Aleatoria de Juez Virtual</option>
                @foreach ($jueces as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
            <input type="hidden" id="codigo" name="codigo" value="">
        </form>
    </div>
</div>