<div class="row mx-3">
    <div class="col">
        <div class="mb-3">
            <label for="entradas" class="form-label">Entradas</label>
            <textarea class="form-control @error('entradas') is-invalid @enderror" id="entradas" name="entradas" rows="5"></textarea>
            @error('entradas')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col">
        <div class="mb-3">
            <label for="salidas" class="form-label">Salidas</label>
            <textarea class="form-control @error('salidas') is-invalid @enderror" id="salidas" name="salidas" rows="5"></textarea>
            @error('salidas')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col">
        <div class="mb-3">
            <label for="puntos" class="form-label">Puntos</label>
            <input type="number" class="form-control @error('puntos') is-invalid @enderror" id="puntos" name="puntos" placeholder="Ej. 5">
            @error('puntos')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="ejemplo" name="ejemplo" value="1" @if(old('ejemplo', true)) checked @endif>
            <label class="form-check-label" for="ejemplo">Es un caso de ejemplo (mostrar entradas y salidas en los resultados)</label>
        </div>
        <button class="btn btn-dark mt-2" type="submit" id="boton_crear"><i class="fa fa-plus me-1"></i> Añadir</button>
        <button type="button" class="btn btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
            <i class="fa fa-eye me-1"></i> Ver Ejemplo
        </button>
        <a href="{{route('problemas.index')}}" class="btn btn-outline-secondary mt-2"><i class="fa fa-arrow-left me-1"></i> Volver</a>
    </div>
</div>
