<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ResolucionCertamenes;

class CertamenCheckDate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token') ?? $request->route('token_certamen');
        $res_certamen = null;

        if ($token) {
            $res_certamen = ResolucionCertamenes::with('certamen')->where('token', $token)->first();
        }

        if (!$res_certamen && $request->filled('token_certamen')) {
            $res_certamen = ResolucionCertamenes::with('certamen')->where('token', $request->token_certamen)->first();
        }

        if (!$res_certamen && $request->filled('id_certamen')) {
            $res_certamen = ResolucionCertamenes::with('certamen')
                ->where('id_usuario', auth()->id())
                ->where('id_certamen', $request->id_certamen)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$res_certamen) {
            $res_certamen = auth()->user()->evaluaciones()->with('certamen')->orderBy('created_at', 'desc')->first();
        }

        if (!$res_certamen || !$res_certamen->certamen) {
            return redirect()->route('certamenes.listado')->with('error', 'No se encontró la evaluación.');
        }

        // Si la evaluación ya fue finalizada por el estudiante o docente, no permitir acceso
        if ($res_certamen->finalizado) {
            return redirect()->route('certamenes.ver', ['id_certamen' => $res_certamen->id_certamen])
                ->with('error', 'No puedes acceder porque esta evaluación ya ha sido finalizada.');
        }

        $_now = Carbon::now();
        $fechaTermino = $res_certamen->fecha_termino_efectiva ?? Carbon::parse($res_certamen->certamen->fecha_termino);

        if ($_now->gte(Carbon::parse($res_certamen->certamen->fecha_inicio)) && $_now->lte($fechaTermino)) {
            $response = $next($request);
            if (method_exists($response, 'header')) {
                $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                         ->header('Pragma', 'no-cache')
                         ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            }
            return $response;
        }

        try {
            DB::beginTransaction();
            $res_certamen->finalizar_certamen();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
        }

        return redirect()->route('certamenes.ver', ['id_certamen' => $res_certamen->id_certamen])
            ->with('error', 'No puedes acceder a esta funcionalidad porque la evaluación ha finalizado.');
    }
}
