<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckUserCertamen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check()){
            // Los administradores y profesores pueden supervisar y navegar libremente sin bloqueos
            if (auth()->user()->hasRole('administrador') || auth()->user()->hasRole('profesor')) {
                return $next($request);
            }

            // Buscar específicamente la última resolución que siga activa (no finalizada)
            $ultimo_res_certamen = auth()->user()->evaluaciones()
                ->where('finalizado', false)
                ->orderBy('updated_at', 'desc')
                ->first();

            if(isset($ultimo_res_certamen)){
                $certamen = $ultimo_res_certamen->certamen;
                if ($certamen) {
                    $_now = Carbon::now();
                    $fechaTermino = $ultimo_res_certamen->fecha_termino_efectiva ?? Carbon::parse($certamen->fecha_termino);
                    if($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte($fechaTermino)){
                        // Si ya está accediendo a la evaluación o su resolución, permitir
                        if ($request->routeIs('certamenes.ver') && (int)$request->route('id_certamen') === (int)$certamen->id) {
                            return $next($request);
                        }
                        if ($request->routeIs('certamenes.resolucion') && $request->route('token') === $ultimo_res_certamen->token) {
                            return $next($request);
                        }
                        return redirect()->route('certamenes.ver', ['id_certamen'=>$certamen->id])->with('error', 'Error: No puedes acceder a otras secciones del sitio porque tienes un certamen pendiente que desarrollar. Recuerda que si has terminado, debes finalizar el certamen.');
                    }else{
                        try{
                            DB::beginTransaction();
                            $ultimo_res_certamen->finalizar_certamen();
                            DB::commit();
                        }catch(\PDOException $e){
                            DB::rollBack();
                        }
                    }
                }
            }
        }
        return $next($request);
    }
}
