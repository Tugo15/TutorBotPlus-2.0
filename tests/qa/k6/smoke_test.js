import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * Prueba de Humo y Carga (Smoke Test) en K6 para TutorBotPlus-2.0
 * Configuración: 1 Usuario Virtual (VU) ejecutando durante 5 segundos
 */
export const options = {
    vus: 1,
    duration: '5s',
    thresholds: {
        http_req_duration: ['p(95)<3000'], // 95% de las peticiones deben responder en menos de 3000ms
        http_req_failed: ['rate<0.05'],    // Tasa de fallos menor al 5%
    },
};

export default function () {
    // URL base local de la aplicación Laravel
    const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

    const res = http.get(BASE_URL);

    check(res, {
        'status es 200 o 302 (redirección a login)': (r) => r.status === 200 || r.status === 302,
        'tiempo de respuesta es aceptable': (r) => r.timings.duration < 3000,
    });

    sleep(1);
}
