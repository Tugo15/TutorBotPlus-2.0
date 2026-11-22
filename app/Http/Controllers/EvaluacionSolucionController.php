<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\JuecesVirtuales;
use App\Models\Problemas;
use App\Models\SolicitudRaLlm;
use App\Models\ResolucionCertamenes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EvaluacionSolucionController extends Controller
{

    private function puedeVerEnvio(EnvioSolucionProblema $envio, bool $requiereDocente = false): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        if ($user->hasRole('administrador')) {
            return true;
        }

        $ownerId = $envio->resolveOwnerId();
        $esDueno = $ownerId !== null && $ownerId === (int) $user->id;
        if (!$requiereDocente && $esDueno) {
            return true;
        }

        $esDocente = $user->hasRole('profesor') ||
            $user->can('ver informe del certamen') ||
            $user->can('ver informe del curso') ||
            $user->can('ver informe del problema');

        if ($esDocente) {
            $cursoId = $envio->resolveCursoId();
            if ($cursoId !== null && $user->cursos()->where('cursos.id', $cursoId)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Si el driver de colas es síncrono, evalúa únicamente el envío actual con throttling por caché
     * (evita ejecutar `app:evaluar-envios` de forma global desde peticiones HTTP).
     */
    private function refrescarPendientesSiSync(EnvioSolucionProblema $envio): void
    {
        if (config('queue.default') !== 'sync') {
            return;
        }

        if ($envio->evaluaciones()->where('estado', '=', 'En Proceso')->exists()) {
            $throttleKey = 'sync_eval_throttle_' . $envio->id;
            if (Cache::add($throttleKey, true, 4)) {
                try {
                    \App\Console\Commands\EvaluarEnvios::evaluarSingleEnvio($envio);
                    $envio->refresh();
                } catch (\Throwable $e) {
                    // Continuar si Judge0 no responde temporalmente
                }
            }
        }
    }

    private function prepararEvaluacionData(EnvioSolucionProblema $envio): array
    {
        $this->refrescarPendientesSiSync($envio);

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
        $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get();
        // Cálculo de intentos restantes de retroalimentación para el dueño del envío
        $cant_retroalimentacion = 0;
        $ownerId = $envio->resolveOwnerId() ?? auth()->id();
        if ($problema && $ownerId) {
            $usadosLlm = DB::table('solicitud_ra_llms')
                ->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')
                ->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')
                ->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')
                ->where('resolver.id_problema', '=', $problema->id)
                ->where('cursa.id_usuario', '=', $ownerId)
                ->count();
            $cant_retroalimentacion = max(0, ($problema->limite_llm ?? 0) - $usadosLlm);
        }
        // Booleano que verifica si hay una retroalimentación asociada al código.
        $tieneRetroalimentacion = SolicitudRaLlm::where('id_envio', '=', $envio->id)->exists();
        $pendientes = $envio->evaluaciones()->where('estado', '=', 'En Proceso')->count();
        $diferencia = 0;
        if (isset($envio->termino) && isset($envio->inicio)) {
            $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
        }
        $res_certamen = null;
        if (isset($envio->id_certamen)) {
            $res_certamen = ResolucionCertamenes::with('certamen.curso')->find($envio->id_certamen);
        }

        return compact('envio', 'evaluaciones', 'highlightjs_choice', 'cant_retroalimentacion', 'tieneRetroalimentacion', 'problema', 'diferencia', 'res_certamen', 'pendientes');
    }

    public function ver_evaluacion(Request $request)
    {
        $envio = EnvioSolucionProblema::with(['curso', 'resolucion_certamen.certamen', 'problema.casos_de_prueba', 'usuario', 'lenguaje'])->where("token", "=", $request->token)->first();
        if (!isset($envio)) {
            return redirect()->route('envios.listado')->with('error', 'El envio no existe');
        }

        if (!$this->puedeVerEnvio($envio)) {
            return redirect()->route('envios.listado')->with('error', 'No tienes permiso para visualizar este envío.');
        }

        return view('plataforma.problemas.resultado', $this->prepararEvaluacionData($envio));
    }

    public function ver_detalle_docente(Request $request)
    {
        $envio = EnvioSolucionProblema::with(['curso', 'resolucion_certamen.certamen', 'problema.casos_de_prueba', 'usuario', 'lenguaje'])->where("token", "=", $request->token)->first();
        if (!isset($envio)) {
            return redirect()->route('home')->with('error', 'El envío no existe');
        }

        if (!$this->puedeVerEnvio($envio, true)) {
            abort(403, 'No tienes permiso para visualizar el detalle de este envío.');
        }

        return view('informes.componentes.detalle_envio', $this->prepararEvaluacionData($envio));
    }

    //Retorna un JSON de los estados de evaluaciones de un envio, esto para utilizar en la vista de resultados de un envio, cuando las evaluaciones aun están en proceso y queremos una actualización de esos.
    public function obtener_status_evaluaciones(Request $request){
        try{
            $envio = EnvioSolucionProblema::with(['curso', 'resolucion_certamen.certamen', 'problema', 'usuario'])->where("token", "=", $request->token)->first();
            if(!isset($envio)){
                return response('El envio no existe', 400);
            }

            if (!$this->puedeVerEnvio($envio)) {
                return response('No tienes permiso para visualizar este envío.', 403);
            }

            $this->refrescarPendientesSiSync($envio);

            $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get()->map(function($item){
                $item->resultado = \App\Support\Judge0StatusHelper::traducir($item->resultado);
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
