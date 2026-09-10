/**
 * Proteccion de inspeccion de elementos e inhabilitacion de F12 y herramientas de desarrollo (DevTools)
 */
(function () {
    // 1. Bloquear atajos de teclado (F12, Ctrl+Shift+I/J/C, Ctrl+U, Cmd+Alt+I/J/C/U)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        if (e.ctrlKey && e.shiftKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        if (e.metaKey && e.altKey && ['I', 'i', 'J', 'j', 'C', 'c', 'U', 'u'].includes(e.key)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        if (e.ctrlKey && ['U', 'u', 'S', 's'].includes(e.key)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // 2. Inhabilitar menú contextual de clic derecho (Inspeccionar)
    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
        return false;
    }, true);

    // 3. Trampa Anti-Debugging (Congela DevTools si logran abrirlo)
    function antiDebugging() {
        var start = performance.now();
        (function () {
            return false;
        })["constructor"]("debugger")();
        var end = performance.now();
        // Si DevTools está abierto, el debugger causa un retraso significativo
        if (end - start > 100) {
            console.clear();
        }
    }

    setInterval(antiDebugging, 500);
})();
