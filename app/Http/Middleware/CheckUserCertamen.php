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
            $ultimo_res_certamen = auth()->user()->evaluaciones()->orderBy('created_at', 'desc')->first();
            if(isset($ultimo_res_certamen) && $ultimo_res_certamen->finalizado == false){
                $certamen = $ultimo_res_certamen->certamen;
                $_now = Carbon::now();
                if($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino))){
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
        return $next($request);
    }
}
