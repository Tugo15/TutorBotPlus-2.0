<div class="modal fade" id="ejemplo_modal" tabindex="-1" role="dialog" aria-labelledby="ejemplo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ejemplo_titulo">Ejemplo de Caso de Prueba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Los problemas de SQL solo requiere un caso de prueba que es el resultado esperado de una consulta de la base de datos. <br>
                    La salida esperada debe estar en un formato de tabla y es de la siguiente manera:
                    <h6>Ejemplo</h6>
                    Utilizando la base de datos de ejemplo llamado <a href="https://www.sqlitetutorial.net/sqlite-sample-database/">"Chinook" de SQLite</a>, se solicita el nombre del artista y el número de álbumes lanzado por artistas, con un límite de cuatro artistas, ordenado de manera descendiente por número de álbumes, salida esperada:
                    <textarea class="form-control" rows="5" disabled>
Iron Maiden|21
Led Zeppelin|14
Deep Purple|11
Metallica|10
                    </textarea>
                    Las columnas deben estar separados entre medio por un | y sigue el orden de selección, en este caso el nombre del artista y el número de álbumes.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
