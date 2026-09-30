<div class="row mx-3">
    <div class="col">
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría de Problemas</label>
            <select class="form-select mb-3" id="categoria" name="categoria" required>
                <option value="">Seleccione una categoría</option>
                @foreach($categorias as $categoria)
                    <option value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                @endforeach
              </select>
            @error('categoria')
                <p class="text-danger text-xs pt-1"> {{ $message }} </p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col d-flex justify-content-end gap-2 me-4">
        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 mb-0" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
            <i class="fa fa-question-circle me-1"></i> Ayuda
        </button>
        <button class="btn btn-sm btn-dark mt-2 mb-0" type="submit" id="add_button"><i class="fa fa-plus me-1"></i> Añadir</button>
    </div>
</div>
