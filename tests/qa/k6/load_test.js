import http from 'k6/http';
import { check, sleep, group } from 'k6';

/**
 * Prueba de Carga (Load Test) para TutorBotPlus-2.0
 * Escenario: Simulación de concurrencia normal en certámenes sincrónicos (hasta 50 usuarios simultáneos)
 */
export const options = {
    stages: [
        { duration: '5s', target: 10 },
        { duration: '15s', target: 30 },
        { duration: '5s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'],
        http_req_failed: ['rate<0.05'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost/xampp/TutorBotPlus-2.0/public';

export default function () {
    group('Navegación e Inicio de Sesión', function () {
        const resLogin = http.get(`${BASE_URL}/login`);
        check(resLogin, {
            'Vista de Login responde HTTP 200': (r) => r.status === 200,
            'Latencia vista login < 2000ms': (r) => r.timings.duration < 2000,
        });
        sleep(1);
    });

    group('Consulta de Cursos y Evaluaciones', function () {
        const resCursos = http.get(`${BASE_URL}/cursos`);
        check(resCursos, {
            'Ruta Cursos responde HTTP 200 o 302': (r) => r.status === 200 || r.status === 302,
        });
        sleep(1);
    });
}
