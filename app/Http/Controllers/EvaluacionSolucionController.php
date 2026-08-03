<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\JuecesVirtuales;
use App\Models\Problemas;
use App\Models\SolicitudRaLlm;
use App\Models\ResolucionCertamenes;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EvaluacionSolucionController extends Controller
{

    public function ver_evaluacion(Request $request)
    {
        $envio = EnvioSolucionProblema::with(['curso','problema', 'usuario'])->where("token", "=", $request->token)->first();
        if(!isset($envio)){
            return redirect()->route('envios.listado')->with('error', 'El envio no existe');
        }
        $problema = $envio->problema;
        $highlightjs_choice = EnvioSolucionProblema::$higlightjs_language[strtolower($envio->lenguaje->abreviatura)];
        $juez = $envio->juez_virtual;
        $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get();
        //Calculo de intentos restante de retroalimentación
        $cant_retroalimentacion = $problema->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')->where('resolver.id_problema', '=', $problema->id)->where('cursa.id_usuario', '=', auth()->user()->id)->count();
        //Booleano que verifica si hay una retroalimentación asociado al código.
        $tieneRetroalimentacion = SolicitudRaLlm::where('id_envio', '=', $envio->id)->exists();
        $pendientes = $envio->evaluaciones()->where('estado', '=', 'En Proceso')->count();
        if(isset($envio->termino)){
            $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
        }
        $res_certamen = null;
        if(isset($envio->id_certamen)){
            $res_certamen = ResolucionCertamenes::find($envio->id_certamen);
        }
        return view('plataforma.problemas.resultado', compact('envio', 'evaluaciones', 'highlightjs_choice', 'cant_retroalimentacion', 'tieneRetroalimentacion', 'problema', 'diferencia', 'res_certamen', 'pendientes'));
    }
    //Retorna un JSON de los estados de evaluaciones de un envio, esto para utilizar en la vista de resultados de un envio, cuando las evaluaciones aun están en proceso y queremos una actualización de esos.
    public function obtener_status_evaluaciones(Request $request){
        try{
            $envio = EnvioSolucionProblema::with(['curso','problema', 'usuario'])->where("token", "=", $request->token)->first();
            if(!isset($envio)){
                return response('El envio no existe', 400);
            }
            $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get()->map(function($item){
                if(isset($item->stout)){
                    $item->stout = base64_decode($item->stout);
                }
                if(isset($item->error_compilacion)){
                    $item->error_compilacion = base64_decode($item->error_compilacion);
                }
                return $item;
            });
        }catch(\PDOException $e){
            return response("Error al contactar con la base de datos", 500);
        }
        return response()->json($evaluaciones, 200);
    }
}
