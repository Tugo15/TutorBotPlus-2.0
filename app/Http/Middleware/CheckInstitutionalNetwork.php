<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Certamenes;

class CheckInstitutionalNetwork
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id_certamen = $request->route('id_certamen') ?? $request->input('id_certamen');

        // Si se encuentra en la ruta por parámetro o token
        if (!$id_certamen && $request->route('token')) {
            $token = $request->route('token');
            $res = \App\Models\ResolucionCertamenes::where('token', $token)->first();
            if ($res) {
                $id_certamen = $res->id_certamen;
            }
        }

        if ($id_certamen) {
            $certamen = Certamenes::find($id_certamen);

            if ($certamen && $certamen->restriccion_red) {
                $clientIp = $request->ip();

                if (!$this->isInstitutionalIp($clientIp, $certamen->ips_autorizadas)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => "Acceso restringido: Esta evaluación solo puede ser rendida desde la red institucional. (IP detectada: {$clientIp})"
                        ], 403);
                    }

                    return redirect()->route('certamenes.listado')->with(
                        'error',
                        "Acceso restringido: Esta evaluación requiere estar conectado a la red institucional (IP detectada: {$clientIp})."
                    );
                }
            }
        }

        return $next($request);
    }

    /**
     * Verifica si una IP está dentro del rango institucional permitido.
     *
     * @param string $ip
     * @param string|null $customRanges
     * @return bool
     */
    protected function isInstitutionalIp(string $ip, ?string $customRanges): bool
    {
        // IPs locales/loopback
        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // Obtener rangos permitidos del env o por defecto subredes privadas e institucionales
        $defaultRanges = config('app.institutional_ip_ranges', env('INSTITUTIONAL_IP_RANGES', '127.0.0.1,10.0.0.0/8,192.168.0.0/16,172.16.0.0/12'));
        
        $allowedList = array_map('trim', explode(',', $defaultRanges));

        if (!empty($customRanges)) {
            $customList = array_map('trim', preg_split('/[\r\n,]+/', $customRanges));
            $allowedList = array_merge($allowedList, $customList);
        }

        foreach ($allowedList as $range) {
            if (empty($range)) continue;
            if ($this->checkIp($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compara una IP individual o rango CIDR.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    protected function checkIp(string $ip, string $range): bool
    {
        if ($ip === $range) {
            return true;
        }

        if (str_contains($range, '/')) {
            list($subnet, $bits) = explode('/', $range, 2);
            $ipNum = ip2long($ip);
            $subnetNum = ip2long($subnet);

            if ($ipNum === false || $subnetNum === false) {
                return false;
            }

            $mask = -1 << (32 - (int)$bits);
            $subnetNum &= $mask;

            return ($ipNum & $mask) === $subnetNum;
        }

        return false;
    }
}
