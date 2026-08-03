<div class="modal fade" id="ejemplo_modal" tabindex="-1" role="dialog" aria-labelledby="ejemplo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ejemplo_titulo">Ayuda sobre el Banco de Problemas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Se deben añadir categorias al banco de problemas (una categoría es un problema) para resolver la evaluación que has creado, debes ingresar al menos una categoría para que la evaluación pueda ser resuelto por un estudiante. <br>
                    <br>Las categorías que se pueden añadir a la evaluación son los que contienen problemas asociados al curso, por lo tanto, debe ingresar categorías en la <a href="{{route("categorias.index")}}">Gestión de Categorías</a>, asociando los problemas a las categorías creadas, estos problemas deben estar asociados al curso correspondiente de la evaluación. <br>
                    <br> <strong class="text-warning">Ojo: Puedes hacer uso de problemas que estén ocultos, el sistema puede seleccionarlo para que sea resuelto durante la evaluación de un estudiante</strong> <br>
                    <br>Es recomendable que tengas categorías creadas especializado para la realización de evaluaciones, asi no utilizar problemas que se utilizan o han sido utilizado durante el desarrollo de ejercicios en clases u otro.<br>
                    <br>Como se menciono anteriormente, una categoría representa un problema a resolver en la evaluación, si la categoría presenta tres problemas distintos, se seleccionará uno de los tres problemas asignados con la categoría de manera aleatoria. </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
