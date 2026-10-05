<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudRaLlm;
use App\Models\LenguajesProgramaciones;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\Problemas;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\DB;


class LlmController extends Controller
{
    public function generar_retroalimentacion(Request $request){
        
        $evaluacion = DB::table('evaluacion_solucions')
        ->join('casos__pruebas', 'casos__pruebas.id', '=', 'evaluacion_solucions.id_caso')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'evaluacion_solucions.id_envio')
        ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
        ->join('lenguajes_programaciones', 'lenguajes_programaciones.id', '=', 'resolver.id_lenguaje')
        ->join('problemas', 'problemas.id', '=', 'resolver.id_problema')
        ->select('evaluacion_solucions.*' ,'envio_solucion_problemas.codigo', 'envio_solucion_problemas.token','lenguajes_programaciones.id as id_lenguaje', 'lenguajes_programaciones.nombre as nombre_lenguaje','problemas.id as id_problema', 'problemas.limite_llm', 'problemas.body_problema_resumido', 'casos__pruebas.entradas', 'casos__pruebas.salidas')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->where(function ($query){
            $query->where('estado', '=', 'Rechazado')->orWhere('estado', '=', 'Error');
        })
        ->orderBy('estado', 'ASC')->first();
        $cant_retroalimentacion = $evaluacion->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')->where('resolver.id_problema', '=', $evaluacion->id)->where('cursa.id_usuario', '=', auth()->user()->id)->count();
        if($cant_retroalimentacion == 0){
            return redirect()->route('envios.ver', ['token'=>$request->token])->with('error', 'Has superado el límite de uso de la LLM');
        }
        $codigo = $evaluacion->codigo;
        $feedbackTexto = null;

        if (env('OPENAI_API_KEY') == 'mock' || empty(env('OPENAI_API_KEY'))) {
            $feedbackTexto = "🤖 [Modo Simulación Bot]: Tu código en " . $evaluacion->nombre_lenguaje . " presentó observaciones. Revisa las variables utilizadas y asegúrate de estructurar adecuadamente la sintaxis y los tipos de datos requeridos por el enunciado.";
        } else {
            if($evaluacion->estado == "Error"){
                if(isset($evaluacion->error_compilacion)){
                    $prompt = SolicitudRaLlm::promptError(base64_decode($evaluacion->error_compilacion), $evaluacion->nombre_lenguaje);
                }else{
                    $prompt = SolicitudRaLlm::promptError(null, $evaluacion->nombre_lenguaje,$evaluacion->resultado);
                }
                try{
                    $result = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => $prompt],
                            ['role' => 'user', 'content' => $codigo],
                        ],
                    ]);
                    $feedbackTexto = $result->choices[0]->message->content;
                }catch(\Exception $e){
                    $feedbackTexto = "🤖 [Modo Simulación Bot - Fallback]: " . $e->getMessage() . ". Revisa la sintaxis de tu código en " . $evaluacion->nombre_lenguaje . ".";
                }
            }else if($evaluacion->estado == "Rechazado"){
                $entradas = $evaluacion->entradas;
                $salidas = $evaluacion->salidas;
                try{
                    $result = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => SolicitudRaLlm::promptErrorRespuestaErronea($entradas, $salidas, base64_decode($evaluacion->stout), $evaluacion->nombre_lenguaje, $evaluacion->body_problema_resumido)],
                            ['role' => 'user', 'content' => $codigo],
                        ],
                    ]);
                    $feedbackTexto = $result->choices[0]->message->content;
                }catch(\Exception $e){
                    $feedbackTexto = "🤖 [Modo Simulación Bot - Fallback]: El código no generó las salidas esperadas. Revisa las condiciones de borde y los tipos de salida.";
                }
            }
        }

        try{
            DB::beginTransaction();
            $retroalimentacion = new SolicitudRaLlm;
            $retroalimentacion->retroalimentacion = $feedbackTexto ?? "Se ha generado la ayuda para tu entrega.";
            $retroalimentacion->id_envio = $evaluacion->id_envio;
            $retroalimentacion->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('envios.ver', ['token'=>$request->token])->with('error', $e->getMessage());
        }
        return redirect()->route('envios.retroalimentacion', ['token'=>$request->token]);
    }

    public function ver_retroalimentacion(Request $request){
        $envios = EnvioSolucionProblema::where('token', '=', $request->token)->first();
        $retroalimentacion = DB::table('solicitud_ra_llms')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'solicitud_ra_llms.id_envio')
        ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
        ->join('problemas', 'resolver.id_problema', '=', 'problemas.id')
        ->select('solicitud_ra_llms.*', 'problemas.id as id_problema', 'problemas.limite_llm', 'problemas.habilitar_llm')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->orderBy('created_at', 'DESC')->first(); 
        $cant_retroalimentacion = $retroalimentacion->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')->where('resolver.id_problema', '=', $retroalimentacion->id_problema)->where('cursa.id_usuario', '=', auth()->user()->id)->count();
        $highlightjs_choice = EnvioSolucionProblema::$higlightjs_language[strtolower($envios->lenguaje->abreviatura)];
        if(!isset($retroalimentacion)){
            return redirect()->route('generar_retroalimentacion', ['token'=>$request->token]);
        }
        return view('plataforma.problemas.retroalimentacion', compact('retroalimentacion', 'cant_retroalimentacion', 'envios', 'highlightjs_choice'))->with('token', $request->token);
    }

    public function verificar_restricciones(Request $request)
    {
        $envio = EnvioSolucionProblema::where('token', '=', $request->token)->first();
        if (!$envio) {
            return redirect()->route('envios.listado')->with('error', 'El envío no existe');
        }

        $res = self::ejecutar_verificacion_restricciones($envio);
        if ($res['estado']) {
            return redirect()->route('envios.ver', ['token' => $request->token])->with('success', 'El bot ha verificado el cumplimiento de restricciones.');
        } else {
            return redirect()->route('envios.ver', ['token' => $request->token])->with('error', $res['mensaje']);
        }
    }

    public static function ejecutar_verificacion_restricciones(EnvioSolucionProblema $envio)
    {
        $problema = $envio->problema;
        if (!$problema || empty(trim($problema->restricciones))) {
            return ['estado' => false, 'mensaje' => 'El problema no posee restricciones definidas.'];
        }

        $codigo = $envio->codigo;
        if (empty($codigo)) {
            return ['estado' => false, 'mensaje' => 'No hay código enviado para evaluar.'];
        }

        $lenguaje = $envio->lenguaje ? $envio->lenguaje->nombre : 'desconocido';

        if (env('OPENAI_API_KEY') == 'mock' || empty(env('OPENAI_API_KEY'))) {
            $simulacion = self::simular_evaluacion_restricciones($codigo, $problema->restricciones, $lenguaje);
            $envio->verificacion_restricciones = $simulacion['respuesta'];
            $envio->cumple_restricciones = $simulacion['cumple'];
            $envio->save();
            return ['estado' => true, 'respuesta' => $simulacion['respuesta'], 'cumple' => $simulacion['cumple']];
        }

        $prompt = SolicitudRaLlm::promptVerificarRestricciones($problema->restricciones, $lenguaje, $problema->body_problema_resumido);

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $codigo],
                ],
            ]);

            $respuesta = $result->choices[0]->message->content;
            $lineas = explode("\n", trim($respuesta));
            $primera_linea = trim($lineas[0]);

            $cumple = (stripos($primera_linea, 'Cumple') !== false && stripos($primera_linea, 'No cumple') === false);

            $envio->verificacion_restricciones = $respuesta;
            $envio->cumple_restricciones = $cumple;
            $envio->save();

            return ['estado' => true, 'respuesta' => $respuesta, 'cumple' => $cumple];
        } catch (\Exception $e) {
            $simulacion = self::simular_evaluacion_restricciones($codigo, $problema->restricciones, $lenguaje);
            $envio->verificacion_restricciones = $simulacion['respuesta'];
            $envio->cumple_restricciones = $simulacion['cumple'];
            $envio->save();

            return ['estado' => true, 'respuesta' => $simulacion['respuesta'], 'cumple' => $simulacion['cumple']];
        }
    }

    public static function simular_evaluacion_restricciones($codigo, $restricciones, $lenguaje)
    {
        $codigo_limpio = preg_replace('!/\*.*?\*/!s', '', $codigo);
        $codigo_limpio = preg_replace('!//.*!', '', $codigo_limpio);
        $codigo_lower = strtolower($codigo_limpio);

        $restricciones_lower = strtolower($restricciones);
        $cumple = true;
        $detalles = [];

        if (preg_match_all('/(?:prohibido\s*(?:usar|utilizar)?|no\s*(?:usar|utilizar|incluir)?|sin|no\s*se\s*permite)\s+([^.,;\n]+)/i', $restricciones, $matches)) {
            foreach ($matches[1] as $targetRaw) {
                $target = strtolower(trim($targetRaw));

                if (preg_match('/\b([a-z_][a-z0-9_]*)\s*\(/i', $targetRaw, $fnMatch)) {
                    $fnName = strtolower($fnMatch[1]);
                    if (preg_match('/\b' . preg_quote($fnName, '/') . '\s*\(/i', $codigo_lower) || str_contains($codigo_lower, '::' . $fnName)) {
                        $cumple = false;
                        $detalles[] = "El código utiliza la función '{$fnName}()', la cual fue prohibida por el profesor.";
                    }
                } elseif (str_contains($target, 'max') && (preg_match('/\bmax\s*\(/i', $codigo_lower) || str_contains($codigo_lower, 'std::max'))) {
                    $cumple = false;
                    $detalles[] = "El código utiliza la función 'max()', lo cual fue prohibido por el profesor.";
                } elseif (str_contains($target, 'min') && (preg_match('/\bmin\s*\(/i', $codigo_lower) || str_contains($codigo_lower, 'std::min'))) {
                    $cumple = false;
                    $detalles[] = "El código utiliza la función 'min()', lo cual fue prohibido por el profesor.";
                } elseif (str_contains($target, 'while') && str_contains($codigo_lower, 'while')) {
                    $cumple = false;
                    $detalles[] = "El código incluye el ciclo 'while', lo cual fue prohibido por el profesor.";
                } elseif (str_contains($target, 'for') && !str_contains($target, 'formato') && str_contains($codigo_lower, 'for')) {
                    $cumple = false;
                    $detalles[] = "El código incluye el ciclo 'for', lo cual fue prohibido por el profesor.";
                } elseif (str_contains($target, 'subconsulta') && (preg_match('/\bselect\b.*\bselect\b/is', $codigo_lower) || str_contains($codigo_lower, 'not in') || str_contains($codigo_lower, 'not exists'))) {
                    $cumple = false;
                    $detalles[] = "La consulta contiene subconsultas prohibidas por el profesor.";
                } elseif (str_contains($target, 'distinct') && str_contains($codigo_lower, 'distinct')) {
                    $cumple = false;
                    $detalles[] = "La consulta incluye 'DISTINCT', lo cual fue prohibido por el profesor.";
                } elseif (str_contains($target, 'reverse') && (str_contains($codigo_lower, 'reverse') || str_contains($codigo_lower, '[::-1]'))) {
                    $cumple = false;
                    $detalles[] = "El código utiliza funciones de inversión de texto prohibidas.";
                }
            }
        }

        if (preg_match_all('/(?:debe\s*(?:usar|utilizar)?|únicamente|unicamente|requerid[oa]|obligatorio)\s+([^.,;\n]+)/i', $restricciones, $reqMatches)) {
            foreach ($reqMatches[1] as $reqRaw) {
                $req = strtolower(trim($reqRaw));

                if (str_contains($req, 'for') && !str_contains($req, 'no for') && !str_contains($codigo_lower, 'for')) {
                    $cumple = false;
                    $detalles[] = "El código no incluye la estructura 'for' solicitada por el profesor.";
                } elseif (str_contains($req, 'while') && !str_contains($req, 'no while') && !str_contains($codigo_lower, 'while')) {
                    $cumple = false;
                    $detalles[] = "El código no incluye la estructura 'while' solicitada por el profesor.";
                } elseif (str_contains($req, 'inner join') && !str_contains($codigo_lower, 'inner join')) {
                    $cumple = false;
                    $detalles[] = "La consulta no utiliza 'INNER JOIN' explícito como lo solicitó el profesor.";
                } elseif (str_contains($req, 'having') && !str_contains($codigo_lower, 'having')) {
                    $cumple = false;
                    $detalles[] = "La consulta no utiliza la cláusula 'HAVING' solicitada por el profesor.";
                } elseif (str_contains($req, 'recursiv') && (str_contains($codigo_lower, 'for') || str_contains($codigo_lower, 'while'))) {
                    $cumple = false;
                    $detalles[] = "El código utiliza ciclos iterativos en lugar de la implementación recursiva solicitada.";
                }
            }
        }

        if ($cumple && (str_contains($restricciones_lower, 'prohibido') || str_contains($restricciones_lower, 'no usar') || str_contains($restricciones_lower, 'sin')) && str_contains($restricciones_lower, 'max') && (preg_match('/\bmax\s*\(/i', $codigo_lower) || str_contains($codigo_lower, 'std::max'))) {
            $cumple = false;
            $detalles[] = "El código utiliza la función 'max()', lo cual fue prohibido por el profesor.";
        }

        if ($cumple) {
            $respuesta = "Cumple\nEl Bot (Modo Simulación Local) ha verificado que tu código en " . $lenguaje . " cumple con la restricción impuesta por el profesor: \"" . $restricciones . "\".";
        } else {
            $respuesta = "No cumple\nEl Bot (Modo Simulación Local) ha verificado que tu código en " . $lenguaje . " no cumple con la restricción del profesor: \"" . $restricciones . "\". " . implode(" ", $detalles);
        }

        return [
            'cumple' => $cumple,
            'respuesta' => $respuesta
        ];
    }
}

