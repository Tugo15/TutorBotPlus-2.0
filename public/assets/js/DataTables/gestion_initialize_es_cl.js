// Desactivar alertas emergentes (popups) de DataTables para manejar celdas vacías o faltantes de manera silenciosa
if (typeof DataTable !== 'undefined') {
    DataTable.ext.errMode = 'none';
}
if (typeof $.fn !== 'undefined' && $.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
}

var indexLastColumn = 0;
var firstRow = $("#table").find('tr')[0];
if (firstRow && firstRow.cells && firstRow.cells.length >= 2) {
    indexLastColumn = firstRow.cells.length - 2;
}

var espaniol = {
    "aria": {
        "sortAscending": ": orden ascendente",
        "sortDescending": ": orden descendente",
        "paginate": {
            "first": "Primera",
            "previous": "Anterior",
            "next": "Siguiente",
            "last": "Última"
        }
    },
    "autoFill": {
        "cancel": "Cancelar",
        "fill": "Rellenar todas las celdas con <i>%d&lt;\\\/i&gt;<\/i>",
        "fillHorizontal": "Rellenar celdas horizontalmente",
        "fillVertical": "Rellenar celdas verticalmente"
    },
    "buttons": {
        "collection": "Colección <span class=\"ui-button-icon-primary ui-icon ui-icon-triangle-1-s\"><\/span>",
        "colvis": "Visibilidad de la columna",
        "colvisRestore": "Restaurar visibilidad",
        "copy": "Copiar",
        "copyKeys": "Presiona ctrl or u2318 + C para copiar los datos de la tabla al portapapeles.<br \/><br \/>Para cancelar, haz click en este mensaje o presiona esc.",
        "copyTitle": "Copiado al portapapeles",
        "csv": "CSV",
        "excel": "Excel",
        "pdf": "PDF",
        "print": "Imprimir",
        "copySuccess": {
            "1": "Copiaste un registro al portapapeles",
            "_": "Copiaste %ds registros al portapapeles"
        },
        "pageLength": {
            "-1": "Mostrar todos",
            "_": "Mostrar %ds filas"
        },
        "createState": "Crear Estado",
        "removeAllStates": "Eliminar Todos los Estados",
        "removeState": "Eliminar",
        "renameState": "Renombrar",
        "savedStates": "Estados Guardados",
        "stateRestore": "Estado %d",
        "updateState": "Actualizar"
    },
    "datetime": {
        "amPm": [
            "AM",
            "PM"
        ],
        "hours": "Horas",
        "minutes": "Minutos",
        "months": {
            "0": "Enero",
            "1": "Febrero",
            "10": "Noviembre",
            "11": "Diciembre",
            "2": "Marzo",
            "3": "Abril",
            "4": "Mayo",
            "5": "Junio",
            "6": "Julio",
            "7": "Agosto",
            "8": "Septiembre",
            "9": "Octubre"
        },
        "next": "Siguiente",
        "previous": "Anterior",
        "seconds": "Segundos",
        "weekdays": [
            "Dom",
            "Lun",
            "Mar",
            "Mie",
            "Jue",
            "Vie",
            "Sab"
        ],
        "unknown": "-"
    },
    "decimal": ",",
    "editor": {
        "close": "Cerrar",
        "create": {
            "button": "Nuevo",
            "submit": "Crear",
            "title": "Crear nuevo registro"
        },
        "edit": {
            "button": "Editar",
            "submit": "Actualizar",
            "title": "Editar registro"
        },
        "error": {
            "system": "Ocurrió un error de sistema (&lt;a target=\"\\\" rel=\"nofollow\" href=\"\\\"&gt;Más información)."
        },
        "multi": {
            "info": "Los elementos seleccionados contienen diferentes valores para esta entrada. Para editar y configurar todos los elementos de esta entrada con el mismo valor, haga clic o toque aquí, de lo contrario, conservarán sus valores individuales.",
            "noMulti": "Esta entrada se puede editar individualmente, pero no como parte de un grupo.",
            "restore": "Deshacer cambios",
            "title": "Múltiples valores"
        },
        "remove": {
            "button": "Eliminar",
            "submit": "Eliminar",
            "title": "Eliminar registro",
            "confirm": {
                "_": "¿Estás seguro de que deseas eliminar %d registros?",
                "1": "¿Estás seguro de que deseas eliminar 1 registro?"
            }
        }
    },
    "emptyTable": "Sin registros",
    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
    "infoFiltered": "(filtrado de _MAX_ registros)",
    "infoThousands": ".",
    "lengthMenu": "Mostrar _MENU_ registros",
    "loadingRecords": "Cargando...",
    "paginate": {
        "first": "<<",
        "last": ">>",
        "next": ">",
        "previous": "<"
    },
    "processing": "Procesando...",
    "search": "Buscar:",
    "searchBuilder": {
        "add": "Agregar Condición",
        "button": {
            "_": "Filtros (%d)",
            "0": "Filtrar"
        },
        "clearAll": "Limpiar Todo",
        "condition": "Condición",
        "conditions": {
            "array": {
                "contains": "Contiene",
                "empty": "Vacío",
                "equals": "Igual",
                "not": "Distinto",
                "notEmpty": "No vacío",
                "without": "Sin"
            },
            "date": {
                "after": "Mayor",
                "before": "Menor",
                "between": "Entre",
                "empty": "Vacío",
                "equals": "Igual",
                "not": "Distinto",
                "notBetween": "No entre",
                "notEmpty": "No vacío"
            },
            "number": {
                "between": "Entre",
                "empty": "Vacío",
                "equals": "Igual",
                "gt": "Mayor",
                "gte": "Mayor o igual",
                "lt": "Menor",
                "lte": "Menor o igual",
                "not": "Distinto",
                "notBetween": "No entre",
                "notEmpty": "No vacío"
            },
            "string": {
                "contains": "Contiene",
                "empty": "Vacío",
                "endsWith": "Termina con",
                "equals": "Igual",
                "not": "Distinto",
                "notEmpty": "No vacío",
                "startsWith": "Comienza con",
                "notContains": "No contiene",
                "notStartsWith": "No comienza con",
                "notEndsWith": "No termina con"
            }
        },
        "data": "Datos",
        "deleteTitle": "Eliminar regla de filtrado",
        "leftTitle": "Filtros anulados",
        "logicAnd": "Y",
        "logicOr": "O",
        "rightTitle": "Filtro",
        "title": {
            "_": "Filtros (%d)",
            "0": "Filtrar"
        },
        "value": "Valor"
    },
    "searchPanes": {
        "clearMessage": "Limpiar todo",
        "collapse": {
            "_": "Paneles de búsqueda (%d)",
            "0": "Paneles de búsqueda"
        },
        "count": "{total}",
        "countFiltered": "{shown} ({total})",
        "emptyPanes": "Sin paneles de búsqueda",
        "loadMessage": "Cargando paneles de búsqueda...",
        "title": "Filtros activos - %d",
        "showMessage": "Mostrar Todos",
        "collapseMessage": "Colapsar Todos"
    },
    "select": {
        "cells": {
            "_": "%d celdas seleccionadas",
            "1": "Una celda seleccionada"
        },
        "columns": {
            "_": "%d columnas seleccionadas",
            "1": "Una columna seleccionada"
        },
        "rows": {
            "1": "Una fila seleccionada",
            "_": "%d filas seleccionadas"
        }
    },
    "thousands": ".",
    "zeroRecords": "No se encontraron registros",
    "searchPlaceholder": "Ingresa un término",
    "stateRestore": {
        "creationModal": {
            "button": "Crear",
            "columns": {
                "search": "Búsqueda de Columnas",
                "visible": "Visibilidad de Columnas"
            },
            "name": "Nombre:",
            "order": "Ordenamiento",
            "paging": "Paginación",
            "scroller": "Posición del Scroll",
            "search": "Búsqueda",
            "searchBuilder": "Constructor de Búsquedas",
            "select": "Seleccionar",
            "title": "Crear un Nuevo Estado",
            "toggleLabel": "Incluir:"
        },
        "duplicateError": "Ya existe un estado con este nombre.",
        "emptyError": "El nombre no puede estar vacío.",
        "emptyStates": "No hay estados guardados",
        "removeConfirm": "¿Estás seguro de que deseas eliminar %s?",
        "removeError": "Hubo un error al eliminar el estado",
        "removeJoiner": "y",
        "removeSubmit": "Eliminar",
        "removeTitle": "Eliminar Estado",
        "renameButton": "Renombrar",
        "renameLabel": "Nombre nuevo para %s:",
        "renameTitle": "Renombrar Estado"
    }
};

var responsiveDomLayout = "<'row mx-0 px-2 px-md-3 pb-2 pt-1 align-items-center gy-2 gx-2'<'col-12 col-lg d-flex align-items-center flex-wrap gap-1 custom-filters-container'l><'col-12 col-lg-auto d-flex align-items-center justify-content-lg-end justify-content-start'f>>" +
    "<'row mx-0'<'col-12 px-0 table-responsive'tr>>" +
    "<'row mx-0 px-2 px-md-4 pt-3 pb-4 align-items-center gy-2'<'col-12 col-md-5 text-center text-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>";

window.getTutorBotFilterKey = function (scope) {
    try {
        var params = new URLSearchParams(window.location.search);
        var idCurso = params.get('id_curso');
        return 'tb_state_' + scope + '_' + window.location.pathname + (idCurso ? '_c' + idCurso : '');
    } catch (e) {
        return 'tb_state_' + scope + '_' + window.location.pathname;
    }
};

window.tutorBotStateCallbacks = function (scope) {
    return {
        stateDuration: 60 * 60 * 24 * 30,
        stateSaveCallback: function (settings, data) {
            try {
                localStorage.setItem(window.getTutorBotFilterKey('dt_' + (settings.sTableId || scope)), JSON.stringify(data));
            } catch (e) {}
        },
        stateLoadCallback: function (settings) {
            try {
                var raw = localStorage.getItem(window.getTutorBotFilterKey('dt_' + (settings.sTableId || scope)));
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                return null;
            }
        }
    };
};

if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.dataTable) {
    function tagTableResponsiveColumns(root) {
        jQuery(root || document).find('table thead th').each(function() {
            var txt = (jQuery(this).text() || '').replace(/\s+/g, ' ').trim().toLowerCase();
            var idx = jQuery(this).index();
            if (txt === 'acción' || txt === 'acciones' || txt === 'accion' || txt === 'opciones') {
                jQuery(this).addClass('no-sort dt-col-acciones');
                if (!jQuery(this).attr('data-priority')) {
                    jQuery(this).attr('data-priority', '2');
                }
            } else if (idx === 0) {
                jQuery(this).addClass('dt-col-primary');
                if (!jQuery(this).attr('data-priority')) {
                    jQuery(this).attr('data-priority', '1');
                }
            } else if (idx === 1 && txt === 'problema') {
                if (!jQuery(this).attr('data-priority')) {
                    jQuery(this).attr('data-priority', '3');
                }
            }
        });
    }
    tagTableResponsiveColumns(document);
    if (typeof DataTable !== 'undefined' && DataTable.defaults) {
        DataTable.defaults.columnDefs = DataTable.defaults.columnDefs || [];
        DataTable.defaults.columnDefs.push(
            { orderable: false, targets: 'no-sort' },
            { responsivePriority: 1, className: 'dt-col-primary', targets: 'dt-col-primary' },
            { responsivePriority: 2, className: 'dt-col-acciones', targets: 'dt-col-acciones' }
        );
        DataTable.defaults.searchDelay = 200;
        DataTable.defaults.autoWidth = false;
    }
    function patchDtrHandlers(api) {
        try {
            var tbodyEl = api.table().body();
            if (!tbodyEl) return;
            jQuery(tbodyEl).find('a[href]:not([href="#"]):not([data-bs-toggle]):not([onclick])')
                .off('click.tbLinkFix mousedown.tbLinkFix mouseup.tbLinkFix')
                .on('click.tbLinkFix mousedown.tbLinkFix mouseup.tbLinkFix', function(ev) {
                    ev.stopPropagation();
                });
            if (jQuery._data) {
                var dtEvents = jQuery._data(tbodyEl, 'events');
                ['click', 'mousedown', 'mouseup'].forEach(function(evType) {
                    if (dtEvents && dtEvents[evType]) {
                        dtEvents[evType].forEach(function(evObj) {
                            if (evObj.namespace === 'dtr' && !evObj._tbWrapped) {
                                var origHandler = evObj.handler;
                                evObj.handler = function(ev) {
                                    if (ev && ev.target && jQuery(ev.target).closest('a, button, input, select, textarea, label, .btn, [data-bs-toggle]').length > 0) {
                                        return;
                                    }
                                    return origHandler.apply(this, arguments);
                                };
                                evObj._tbWrapped = true;
                            }
                        });
                    }
                });
            }
        } catch (err) {}
    }

    jQuery(document).on('preInit.dt', function(e, settings) {
        if (settings && settings.nTable) {
            tagTableResponsiveColumns(settings.nTable);
        }
    });
    jQuery(document).on('init.dt', function(e, settings) {
        var api = new jQuery.fn.dataTable.Api(settings);
        patchDtrHandlers(api);
        setTimeout(function() {
            patchDtrHandlers(api);
            try {
                api.columns.adjust();
                if (api.responsive && typeof api.responsive.recalc === 'function') {
                    api.responsive.recalc();
                }
            } catch (err) {}
        }, 120);
    });
    jQuery(document).on('draw.dt', function(e, settings) {
        if (settings && settings.nTable) {
            patchDtrHandlers(new jQuery.fn.dataTable.Api(settings));
        }
    });

    function recalcAllDataTablesOnViewportChange() {
        try {
            if (!jQuery.fn || !jQuery.fn.dataTable) return;
            var tables = jQuery.fn.dataTable.tables({ visible: true, api: true });
            if (!tables || !tables.tables) return;
            tables.tables().every(function() {
                var api = this;
                try {
                    var node = api.table().node();
                    var wrapper = api.table().container();
                    var tblContainer = wrapper ? (wrapper.querySelector('.table-responsive') || (node && node.parentElement)) : null;
                    if (tblContainer && tblContainer.dataset && tblContainer.dataset.dtMaxH) {
                        delete tblContainer.dataset.dtMaxH;
                        tblContainer.style.minHeight = '';
                    }
                    api.columns.adjust();
                    if (api.responsive && typeof api.responsive.recalc === 'function') {
                        api.responsive.recalc();
                    }
                    patchDtrHandlers(api);
                } catch (innerErr) {}
            });
        } catch (err) {}
    }

    var tbResizeTimer1 = null;
    var tbResizeTimer2 = null;
    window.addEventListener('resize', function() {
        clearTimeout(tbResizeTimer1);
        clearTimeout(tbResizeTimer2);
        tbResizeTimer1 = setTimeout(recalcAllDataTablesOnViewportChange, 60);
        tbResizeTimer2 = setTimeout(recalcAllDataTablesOnViewportChange, 260);
    });
    jQuery(document).on('draw.dt init.dt', function(e, settings) {
        var wrapper = settings && settings.nTableWrapper ? settings.nTableWrapper : document;
        var map = { 'First': 'Primera', 'Previous': 'Anterior', 'Next': 'Siguiente', 'Last': 'Última' };
        jQuery(wrapper).find('.dt-paging-button[aria-label], .paginate_button[aria-label]').each(function() {
            var lbl = this.getAttribute('aria-label');
            if (map[lbl]) {
                this.setAttribute('aria-label', map[lbl]);
                if (!this.getAttribute('title')) this.setAttribute('title', map[lbl]);
            }
        });

        // Evitar IDs duplicados (dt-length-0 / dt-search-0) o atributos id="" cuando hay múltiples DataTables en una misma vista
        if (wrapper && wrapper !== document) {
            jQuery(wrapper).find('[id=""]').removeAttr('id');
            var tableId = settings && settings.nTable && settings.nTable.id ? settings.nTable.id : '';
            if (tableId) {
                var lenSelect = wrapper.querySelector('select[id^="dt-length-"]');
                if (lenSelect && lenSelect.id !== 'dt-length-' + tableId) {
                    var oldLenId = lenSelect.id;
                    var newLenId = 'dt-length-' + tableId;
                    lenSelect.id = newLenId;
                    var lenLbl = wrapper.querySelector('label[for="' + oldLenId + '"]');
                    if (lenLbl) lenLbl.setAttribute('for', newLenId);
                }
                var searchInput = wrapper.querySelector('input[id^="dt-search-"]');
                if (searchInput && searchInput.id !== 'dt-search-' + tableId) {
                    var oldSearchId = searchInput.id;
                    var newSearchId = 'dt-search-' + tableId;
                    searchInput.id = newSearchId;
                    var searchLbl = wrapper.querySelector('label[for="' + oldSearchId + '"]');
                    if (searchLbl) searchLbl.setAttribute('for', newSearchId);
                }
            }
        }

        // Mantener estabilidad vertical en tablas paginadas para evitar saltos de scroll o colapso de altura al cambiar de página
        if (wrapper && wrapper !== document && settings && settings.nTable) {
            try {
                var apiPage = new jQuery.fn.dataTable.Api(settings);
                var info = apiPage.page.info();
                var tblContainer = wrapper.querySelector('.table-responsive') || settings.nTable.parentElement;
                if (tblContainer && info) {
                    var prevLen = tblContainer.dataset.dtPageLen ? parseInt(tblContainer.dataset.dtPageLen, 10) : info.length;
                    if (prevLen !== info.length) {
                        delete tblContainer.dataset.dtMaxH;
                        tblContainer.style.minHeight = '';
                        tblContainer.dataset.dtPageLen = String(info.length);
                    } else if (!tblContainer.dataset.dtPageLen) {
                        tblContainer.dataset.dtPageLen = String(info.length);
                    }

                    if (info.pages > 1 && info.length > 0) {
                        var shownRows = info.end - info.start;
                        var tableH = Math.ceil(settings.nTable.getBoundingClientRect().height);
                        var storedMax = tblContainer.dataset.dtMaxH ? parseInt(tblContainer.dataset.dtMaxH, 10) : 0;

                        if (shownRows >= info.length) {
                            storedMax = Math.max(storedMax, tableH);
                            tblContainer.dataset.dtMaxH = String(storedMax);
                        } else if (!storedMax && shownRows > 0) {
                            var theadEl = settings.nTable.querySelector('thead');
                            var tbodyRows = settings.nTable.querySelectorAll('tbody tr');
                            var theadH = theadEl ? theadEl.getBoundingClientRect().height : 42;
                            var avgRowH = tbodyRows.length ? ((tableH - theadH) / tbodyRows.length) : 52;
                            storedMax = Math.ceil(theadH + avgRowH * info.length);
                            tblContainer.dataset.dtMaxH = String(storedMax);
                        }

                        if (storedMax > 0) {
                            tblContainer.style.minHeight = storedMax + 'px';
                        } else {
                            tblContainer.style.minHeight = '';
                        }
                    } else {
                        delete tblContainer.dataset.dtMaxH;
                        tblContainer.style.minHeight = '';
                    }
                }
            } catch (err) {}
        }
    });
}

if ($('#table').length > 0) {
    var dtMain = new DataTable('#table', Object.assign({
        language : espaniol,
        responsive: true,
        autoWidth: false,
        stateSave: true,
        columnDefs: [
            { defaultContent: "-", targets: "_all" },
            { orderable: false, targets: "no-sort" }
        ],
        order: [[indexLastColumn, 'desc']],
        dom: responsiveDomLayout,
        initComplete: function() {
            var api = this.api();
            setTimeout(function() {
                api.columns.adjust();
                if (api.responsive && typeof api.responsive.recalc === 'function') {
                    api.responsive.recalc();
                }
            }, 120);
        }
    }, window.tutorBotStateCallbacks('table')));
    $(window).on('load', function() {
        if (dtMain && dtMain.columns) {
            dtMain.columns.adjust();
            if (dtMain.responsive && typeof dtMain.responsive.recalc === 'function') {
                dtMain.responsive.recalc();
            }
        }
    });
}

if ($('#table_evaluaciones').length > 0) {
    var dtEval = new DataTable('#table_evaluaciones', Object.assign({
        language : espaniol,
        responsive: true,
        autoWidth: false,
        stateSave: true,
        columnDefs: [
            { defaultContent: "-", targets: "_all" },
            { orderable: false, targets: "no-sort" }
        ],
        dom: responsiveDomLayout,
        initComplete: function() {
            var api = this.api();
            setTimeout(function() {
                api.columns.adjust();
                if (api.responsive && typeof api.responsive.recalc === 'function') {
                    api.responsive.recalc();
                }
            }, 120);
        }
    }, window.tutorBotStateCallbacks('table_evaluaciones')));
    $(window).on('load', function() {
        if (dtEval && dtEval.columns) {
            dtEval.columns.adjust();
            if (dtEval.responsive && typeof dtEval.responsive.recalc === 'function') {
                dtEval.responsive.recalc();
            }
        }
    });
}
