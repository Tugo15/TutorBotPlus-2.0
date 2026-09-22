function checkRut(rut) {
    // 1. Filtrar cualquier carácter que no sea número (0-9) ni letra K/k
    var raw = rut.value.replace(/[^0-9kK]/g, '');

    // 2. Solo permitir K/k como último carácter (dígito verificador).
    // Si la K/k fue ingresada en medio del RUT, se remueve.
    if (raw.length > 1) {
        var bodyPart = raw.slice(0, -1).replace(/[kK]/g, '');
        var lastChar = raw.slice(-1).toUpperCase();
        raw = bodyPart + lastChar;
    } else if (raw.length === 1) {
        raw = raw.toUpperCase();
    }

    if (raw.length === 0) {
        rut.value = '';
        rut.setCustomValidity('');
        return;
    }

    // 3. Formatear como XXXXXXXX-DV
    var cuerpo = '';
    var dv = '';

    if (raw.length > 1) {
        cuerpo = raw.slice(0, -1);
        dv = raw.slice(-1).toUpperCase();
        rut.value = cuerpo + '-' + dv;
    } else {
        cuerpo = raw;
        dv = '';
        rut.value = raw;
    }

    // 4. Validar longitud mínima del cuerpo (7 a 8 dígitos)
    if (cuerpo.length < 7 || dv === '') {
        rut.setCustomValidity("RUT incompleto");
        return false;
    }

    // 5. Algoritmo Módulo 11 de validación de RUT chileno
    var suma = 0;
    var multiplo = 2;

    for (var i = 1; i <= cuerpo.length; i++) {
        var digit = parseInt(cuerpo.charAt(cuerpo.length - i), 10);
        suma += digit * multiplo;
        if (multiplo < 7) {
            multiplo++;
        } else {
            multiplo = 2;
        }
    }

    var resto = suma % 11;
    var dvEsperado = 11 - resto;
    var dvCalc = '';

    if (dvEsperado === 11) {
        dvCalc = '0';
    } else if (dvEsperado === 10) {
        dvCalc = 'K';
    } else {
        dvCalc = dvEsperado.toString();
    }

    if (dvCalc !== dv) {
        rut.setCustomValidity("RUT inválido (Dígito verificador incorrecto)");
        return false;
    }

    rut.setCustomValidity('');
    return true;
}