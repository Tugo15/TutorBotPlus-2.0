import http from 'k6/http';
import { check, sleep, group } from 'k6';

/**
 * Prueba de Estrés (Stress Test) para TutorBotPlus-2.0
 * Escenario: Simulación de ráfagas masivas de entregas simultáneas (hasta 100 usuarios virtuales)
 * Propósito: Determinar el punto de ruptura del servidor y medir la recuperación del sistema.
 */
export const options = {
    stages: [
        { duration: '10s', target: 20 },  // Carga inicial
        { duration: '20s', target: 60 },  // Incremento rápido
        { duration: '20s', target: 100 }, // Pico de estrés masivo (100 usuarios)
        { duration: '10s', target: 0 },   // Enfriamiento
    ],
    thresholds: {
        http_req_duration: ['p(95)<4000'], // Latencia límite p95 < 4s en pico
        http_req_failed: ['rate<0.10'],    // Tolerancia a fallos < 10% en estrés
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
    group('Carga Masiva en Certámenes y Envíos', function () {
        const resHome = http.get(BASE_URL);
        check(resHome, {
            'Respuesta base en pico de estrés': (r) => r.status === 200 || r.status === 302,
        });

        const resLogin = http.get(`${BASE_URL}/login`);
        check(resLogin, {
            'Login disponible bajo estrés': (r) => r.status === 200,
        });

        sleep(0.5);
    });
}
