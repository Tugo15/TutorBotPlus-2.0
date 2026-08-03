<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class CertamenCheckDate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ultimo_res_certamen = auth()->user()->evaluaciones()->with('certamen')->orderBy('created_at', 'desc')->first();
        $_now = Carbon::now();
        if($_now->gte(Carbon::parse($ultimo_res_certamen->certamen->fecha_inicio)) && $_now->lte(Carbon::parse($ultimo_res_certamen->certamen->fecha_termino))){
            return $next($request);
        }
        try{
            DB::beginTransaction();
            $ultimo_res_certamen->finalizar_certamen();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
        }
        return redirect()->route('certamenes.ver', ['id_certamen'=>$ultimo_res_certamen->id_certamen])->with('error', 'No puedes acceder a esta funcionalidad porque la evaluación ha finalizado.');

    }
}
