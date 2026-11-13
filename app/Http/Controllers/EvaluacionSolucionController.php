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

use Illuminate\Support\Facades\Artisan;

class EvaluacionSolucionController extends Controller
{

    public function ver_evaluacion(Request $request)
    {
        $envio = EnvioSolucionProblema::with(['curso', 'problema.casos_de_prueba', 'usuario', 'lenguaje'])->where("token", "=", $request->token)->first();
        if(!isset($envio)){
            return redirect()->route('envios.listado')->with('error', 'El envio no existe');
        }

        $esDocenteOAdmin = auth()->check() && (
            auth()->user()->hasRole('administrador') ||
            auth()->user()->hasRole('profesor') ||
            auth()->user()->can('ver informe del certamen') ||
            auth()->user()->can('ver informe del curso') ||
            auth()->user()->can('ver informe del problema')
        );
        if (!$esDocenteOAdmin && (!isset($envio->usuario) || $envio->usuario->id !== auth()->id())) {
            return redirect()->route('envios.listado')->with('error', 'No tienes permiso para visualizar este envío.');
        }

        // Si existen evaluaciones pendientes en proceso, evaluar de forma sincrónica con Judge0 inmediatamente
        if ($envio->evaluaciones()->where('estado', '=', 'En Proceso')->exists()) {
            try {
                Artisan::call('app:evaluar-envios');
                $envio->refresh();
            } catch (\Exception $e) {
                // Continuar si ocurre alguna excepción no crítica
            }
        }

        $problema = $envio->problema;
        if ($problema && !empty(trim($problema->restricciones)) && is_null($envio->verificacion_restricciones)) {
            try {
                LlmController::ejecutar_verificacion_restricciones($envio);
                $envio->refresh();
            } catch (\Exception $e) {
                // Si falla la IA, no bloquea el flujo visualización de la entrega
            }
        }
        $highlightjs_choice = 'c';
        if (isset($envio->lenguaje) && isset($envio->lenguaje->abreviatura)) {
            $abrev = strtolower($envio->lenguaje->abreviatura);
            if (isset(EnvioSolucionProblema::$higlightjs_language[$abrev])) {
                $highlightjs_choice = EnvioSolucionProblema::$higlightjs_language[$abrev];
            }
        }
        $juez = $envio->juez_virtual;
        $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get();
        //Calculo de intentos restante de retroalimentación
        $cant_retroalimentacion = 0;
        if ($problema && auth()->check()) {
            $cant_retroalimentacion = ($problema->limite_llm ?? 0) - DB::table('solicitud_ra_llms')
                ->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')
                ->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')
                ->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')
                ->where('resolver.id_problema', '=', $problema->id)
                ->where('cursa.id_usuario', '=', auth()->user()->id)
                ->count();
        }
        //Booleano que verifica si hay una retroalimentación asociado al código.
        $tieneRetroalimentacion = SolicitudRaLlm::where('id_envio', '=', $envio->id)->exists();
        $pendientes = $envio->evaluaciones()->where('estado', '=', 'En Proceso')->count();
        $diferencia = 0;
        if(isset($envio->termino) && isset($envio->inicio)){
            $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
        }
        $res_certamen = null;
        if(isset($envio->id_certamen)){
            $res_certamen = ResolucionCertamenes::with('certamen.curso')->find($envio->id_certamen);
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

            // Si al consultar vía AJAX existen evaluaciones pendientes, forzar actualización sincrónica
            if ($envio->evaluaciones()->where('estado', '=', 'En Proceso')->exists()) {
                Artisan::call('app:evaluar-envios');
                $envio->refresh();
            }

            $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get()->map(function($item){
                $item->stout = $item->salida_texto;
                $item->error_compilacion = $item->error_compilacion_texto;
                return $item;
            });
        }catch(\PDOException $e){
            return response("Error al contactar con la base de datos", 500);
        }
        return response()->json($evaluaciones, 200);
    }
}
