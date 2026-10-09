<div class="row mx-3 gy-3">
    <div class="col-12 col-lg-4">
        <div class="mb-3">
            <label for="entradas" class="form-label font-weight-bold text-xs text-dark">Entradas</label>
            <textarea class="form-control @error('entradas') is-invalid @enderror" id="entradas" name="entradas" rows="5" placeholder="Entradas del caso"></textarea>
            @error('entradas')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="mb-3">
            <label for="salidas" class="form-label font-weight-bold text-xs text-dark">Salidas</label>
            <textarea class="form-control @error('salidas') is-invalid @enderror" id="salidas" name="salidas" rows="5" placeholder="Salidas esperadas"></textarea>
            @error('salidas')
                <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
            @enderror
        </div>
    </div>
    <div class="col-12 col-lg-4 d-flex flex-column justify-content-between">
        <div>
            <div class="mb-3">
                <label for="puntos" class="form-label font-weight-bold text-xs text-dark">Puntos</label>
                <input type="number" step="any" class="form-control @error('puntos') is-invalid @enderror" id="puntos" name="puntos" placeholder="Ej. 5">
                @error('puntos')
                    <p class="text-danger text-xs pt-1 mb-0"> {{ $message }} </p>
                @enderror
            </div>
            <div class="form-check form-switch d-flex align-items-start gap-2 ps-0 mb-3 border p-2 rounded bg-light">
                <input class="form-check-input ms-0 flex-shrink-0" type="checkbox" role="switch" id="ejemplo" name="ejemplo" value="1" @if(old('ejemplo', true)) checked @endif style="cursor: pointer; width: 2.5em; height: 1.25em;">
                <label class="form-check-label text-xs font-weight-bold text-dark text-wrap mb-0 cursor-pointer" for="ejemplo">
                    Es un caso de ejemplo <span class="text-secondary font-weight-normal d-block text-xxs">(mostrar entradas y salidas en los resultados)</span>
                </label>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-sm btn-dark mb-0" type="submit" id="boton_crear"><i class="fa fa-plus me-1"></i> Añadir</button>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-0" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
                <i class="fa fa-eye me-1"></i> Ver Ejemplo
            </button>
        </div>
    </div>
</div>
