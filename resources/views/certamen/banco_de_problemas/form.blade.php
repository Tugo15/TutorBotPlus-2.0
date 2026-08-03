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
<div class="col d-flex justify-content-end">
    <button class="btn btn-primary mt-2" type="submit" id="add_button">Añadir</button>
    <button type="button" class="btn bg-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#ejemplo_modal">
        Ayuda
    </button>
    <a href="{{route('certamen.index')}}" class="btn bg-outline-primary mt-2 me-5">Volver</a>
</div>
</div>
