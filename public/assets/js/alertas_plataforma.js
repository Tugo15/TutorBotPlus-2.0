function submitCodigo() {
    Swal.fire({
        title: "¿Estás seguro de que quieres subir el código? Asegúrate de que todo esté correcto",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector('#codigo').value = editor.getValue();
            let send_button = document.querySelector('#boton_enviar_solucion');
            let save_button = document.querySelector('#boton_guardar');
            send_button.setAttribute('disabled', "");
            save_button.setAttribute('disabled',"");
            send_button.innerText = "Enviando Código...";
            document.getElementById('evaluacion_form').submit();
        }
    });
}

function solicitarRetroalimentacion(ev) {
    ev.preventDefault();
    var urlToRedirect = ev.currentTarget.getAttribute('href');
    Swal.fire({
        title: "¿Estás seguro de que solicitar retroalimentación?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            let boton_ra = document.querySelector('#boton_ra');
            boton_ra.classList.add('disabled');
            boton_ra.innerText = "Generando Retroalimentación";
            window.location.href = urlToRedirect;
        }
    });
}