function submitCodigo() {
    var codigoActual = (typeof editor !== 'undefined' && editor) ? editor.getValue() : '';
    if (!codigoActual || codigoActual.trim().length === 0) {
        if (typeof formSubmited !== 'undefined') {
            formSubmited = false;
        }
        Swal.fire({
            title: "Código vacío",
            text: "Debes escribir al menos una línea de código antes de enviar tu solución.",
            icon: "warning",
            confirmButtonText: "Entendido"
        });
        return;
    }
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
            if (send_button) send_button.setAttribute('disabled', "");
            if (save_button) save_button.setAttribute('disabled',"");
            if (send_button) send_button.innerText = "Enviando Código...";
            document.getElementById('evaluacion_form').submit();
        } else {
            if (typeof formSubmited !== 'undefined') {
                formSubmited = false;
            }
        }
    });
}

function solicitarRetroalimentacion(ev) {
    ev.preventDefault();
    var targetBtn = ev.currentTarget;
    if (targetBtn && targetBtn.dataset.loading === '1') return;
    var urlToRedirect = targetBtn ? targetBtn.getAttribute('href') : '';
    Swal.fire({
        title: "¿Estás seguro de solicitar retroalimentación?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Sí",
        denyButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            let boton_ra = targetBtn || document.querySelector('#boton_ra');
            if (boton_ra) {
                boton_ra.dataset.loading = '1';
                boton_ra.classList.add('disabled');
                boton_ra.setAttribute('aria-disabled', 'true');
                boton_ra.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Generando Retroalimentación...';
            }
            if (urlToRedirect) {
                window.location.href = urlToRedirect;
            }
        }
    });
}