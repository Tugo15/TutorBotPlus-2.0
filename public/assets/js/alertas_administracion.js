var adminFormSubmitting = false;

function submitFormEliminar(item, id) {
    if (adminFormSubmitting) return;
    Swal.fire({
        title: "¿Estás seguro que quieres eliminar " + item + "?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Sí",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.getElementById('eliminarForm_' + id);
            if (form) {
                adminFormSubmitting = true;
                form.submit();
            }
        }
    });
}

function validarFormularioAdmin(form) {
    if (!form) return true;
    if (typeof window.syncAdminEditors === 'function') {
        try { window.syncAdminEditors(); } catch (e) {}
    }
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
        return false;
    }
    var requiredHidden = form.querySelectorAll('input[type="hidden"][data-required-label]');
    for (var i = 0; i < requiredHidden.length; i++) {
        if (!requiredHidden[i].value || requiredHidden[i].value.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio faltante',
                text: 'Por favor complete el campo: ' + requiredHidden[i].getAttribute('data-required-label') + '.',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    }
    return true;
}

function submitFormCrear() {
    if (adminFormSubmitting) return;
    var form = document.getElementById('crearForm');
    if (!validarFormularioAdmin(form)) return;
    Swal.fire({
        title: "¿Estás seguro de que los datos están correctos?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Sí",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            if (form) {
                adminFormSubmitting = true;
                form.submit();
            }
        }
    });
}

function submitFormEditar(item) {
    if (adminFormSubmitting) return;
    var form = document.getElementById('editarForm');
    if (!validarFormularioAdmin(form)) return;
    Swal.fire({
        title: "¿Estás seguro que quieres editar " + item + "?",
        icon: "warning",
        showDenyButton: true,
        confirmButtonText: "Sí",
        denyButtonText: `No`
    }).then((result) => {
        if (result.isConfirmed) {
            if (form) {
                adminFormSubmitting = true;
                form.submit();
            }
        }
    });
}

function toggleSelectionCards(containerId, state) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var checkboxes = container.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function(cb) {
        if (cb.disabled) return;
        cb.checked = state;
        var card = cb.closest('.selection-card');
        if (card) card.classList.toggle('checked', state);
    });
}

function verEtiquetasModal(titulo, etiquetas, tipoColor = 'info', icono = 'fa-tag') {
    if (!etiquetas || etiquetas.length === 0) {
        Swal.fire({
            title: titulo,
            text: 'No hay elementos para mostrar.',
            icon: 'info',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#344767'
        });
        return;
    }

    let borderClass = 'border-' + tipoColor;
    let textClass = 'text-' + tipoColor;

    let html = '<div class="text-start mb-2"><small class="text-muted">Total de elementos: <strong>' + etiquetas.length + '</strong></small></div>';
    html += '<div class="d-flex flex-wrap gap-1 p-3 border rounded bg-light" style="max-height: 380px; overflow-y: auto;">';
    etiquetas.forEach(item => {
        let texto = typeof item === 'object' && item.nombre ? item.nombre + (item.codigo ? ' (' + item.codigo + ')' : '') : item;
        html += '<span class="badge border ' + borderClass + ' ' + textClass + ' text-xxs px-2.5 py-1.5 bg-white m-0.5 shadow-xs"><i class="fa ' + icono + ' me-1 opacity-7"></i>' + texto + '</span>';
    });
    html += '</div>';

    Swal.fire({
        title: '<h6 class="mb-0 font-weight-bold text-dark">' + titulo + '</h6>',
        html: html,
        width: 580,
        confirmButtonText: '<i class="fa fa-times me-1"></i> Cerrar',
        confirmButtonColor: '#344767'
    });
}

function verPermisosModal(rolNombre, permisos) {
    verEtiquetasModal('Permisos del Rol: ' + rolNombre, permisos, 'info', 'fa-key');
}

function verCursosModal(titulo, cursos) {
    if (!cursos || cursos.length === 0) {
        Swal.fire({
            title: titulo,
            text: 'No tiene cursos asignados.',
            icon: 'info',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#344767'
        });
        return;
    }
    let html = '<div class="text-start mb-2"><small class="text-muted">Total de cursos asignados: <strong>' + cursos.length + '</strong></small></div>';
    html += '<div class="d-flex flex-wrap gap-1 p-3 border rounded bg-light" style="max-height: 380px; overflow-y: auto;">';
    cursos.forEach(c => {
        html += '<span class="badge border border-primary text-primary text-xxs px-2.5 py-1.5 bg-white m-0.5 shadow-xs d-inline-flex align-items-center"><i class="fa fa-graduation-cap text-warning me-1.5"></i>' + c.nombre + ' <strong class="ms-1 font-monospace text-dark">(' + c.codigo + ')</strong></span>';
    });
    html += '</div>';

    Swal.fire({
        title: '<h6 class="mb-0 font-weight-bold text-dark">' + titulo + '</h6>',
        html: html,
        width: 620,
        confirmButtonText: '<i class="fa fa-times me-1"></i> Cerrar',
        confirmButtonColor: '#344767'
    });
}

function confirmarAccionForm(event, form, titulo, texto, confirmText, isDanger) {
    if (event) event.preventDefault();
    if (adminFormSubmitting) return false;
    Swal.fire({
        title: titulo || '¿Estás seguro?',
        text: texto || '',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText || 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: isDanger === false ? '#344767' : '#d33',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed && form) {
            adminFormSubmitting = true;
            var btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            form.submit();
        }
    });
    return false;
}