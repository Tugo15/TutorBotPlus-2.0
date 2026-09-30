import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * Prueba de Humo (Smoke Test) en K6 para TutorBotPlus-2.0
 */
export const options = {
    vus: 1,
    duration: '5s',
    thresholds: {
        http_req_duration: ['p(95)<3000'],
        http_req_failed: ['rate<0.05'],
    },
};

export default function () {
    const BASE_URL = __ENV.BASE_URL || 'http://localhost/xampp/TutorBotPlus-2.0/public';

    const res = http.get(BASE_URL);

    check(res, {
        'status es 200 o 302': (r) => r.status === 200 || r.status === 302,
        'tiempo de respuesta es aceptable': (r) => r.timings.duration < 3000,
    });

    sleep(1);
}
