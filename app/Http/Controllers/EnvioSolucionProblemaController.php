<?php

namespace App\Http\Controllers;

use App\Models\Casos_Pruebas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\JuecesVirtuales;
use App\Models\LenguajesProgramaciones;
use App\Models\Problemas;
use App\Models\Resolver;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Storage;
class EnvioSolucionProblemaController extends Controller
{
    public function ver_envios(Request $request){
        $ultima_evaluacion = DB::table('evaluacion_solucions')
        ->select(
            'id_envio',
            DB::raw("MAX(CASE WHEN resultado = 'Aceptado (Variación de Formato)' THEN 1 ELSE 0 END) as variacion_formato"),
            DB::raw("MAX(CASE WHEN estado IN ('Rechazado', 'En Proceso', 'Error') THEN resultado ELSE NULL END) as resultado"),
            DB::raw("MAX(CASE WHEN estado IN ('Rechazado', 'En Proceso', 'Error') THEN estado ELSE NULL END) as estado")
        )
        ->groupBy('id_envio');

        $envios_query = DB::table('envio_solucion_problemas')
        ->leftJoinSub($ultima_evaluacion, 'ultima_evaluacion', function (JoinClause $join){
            $join->on('envio_solucion_problemas.id', '=', 'ultima_evaluacion.id_envio');
        })
        ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
        ->join('problemas', 'problemas.id', '=', 'resolver.id_problema')
        ->join('casos__pruebas', 'casos__pruebas.id_problema', '=', 'problemas.id')
        ->join('lenguajes_programaciones', 'lenguajes_programaciones.id', '=', 'resolver.id_lenguaje')
        ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
        ->join('cursos', 'cursos.id', '=', 'cursa.id_curso')
        ->whereNull('id_certamen')
        ->where('cursa.id_usuario', '=', auth()->user()->id)
        ->whereNotNull('termino')
        ->select('problemas.nombre as nombre_problema', 'problemas.codigo as codigo_problema', 'problemas.restricciones', 'envio_solucion_problemas.id as id_envio', 'envio_solucion_problemas.created_at','envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje', 'envio_solucion_problemas.cumple_restricciones', 'lenguajes_programaciones.nombre as nombre_lenguaje', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'cursos.nombre as nombre_curso', 'cursos.id as id_curso', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado', 'ultima_evaluacion.variacion_formato', DB::raw('count(casos__pruebas.id) as total_casos'))
        ->groupBy('problemas.nombre', 'problemas.codigo', 'problemas.restricciones', 'envio_solucion_problemas.id', 'envio_solucion_problemas.created_at','envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje', 'envio_solucion_problemas.cumple_restricciones', 'lenguajes_programaciones.nombre', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'cursos.nombre', 'cursos.id', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado', 'ultima_evaluacion.variacion_formato')
        ->orderBy('envio_solucion_problemas.created_at', 'DESC');
        if(isset($request->id_problema)){
            $envios_query = $envios_query->where('problemas.id', '=', $request->id_problema);
        }
        $envios = $envios_query->get()->map(function ($envio){
            $envio->inicio = Carbon::parse($envio->inicio)->locale('es_ES')->isoFormat('lll');
            $envio->termino = Carbon::parse($envio->termino)->locale('es_ES')->isoFormat('lll');
            return $envio;
        });

        return view('plataforma.envios.index', compact('envios'));
    }

    public function enviar_solucion(Request $request)
    {
        $validated = $request->validate(EnvioSolucionProblema::$rules);
        //Escoge un juez virtual de manera aleatoria si el usuario escogió eso.
        if ($request->juez_virtual == "0" || empty($request->juez_virtual)) {
            $juezDef = JuecesVirtuales::first();
            $request->juez_virtual = $juezDef ? $juezDef->id : 1;
        }
        try {
            DB::beginTransaction();
            if(isset($request->id_certamen)){
                $envio = EnvioSolucionProblema::where('id_resolver', '=', $request->id_resolver)->where('id_certamen', '=', $request->id_certamen)->orderBy('created_at', 'DESC')->first();
            }else{
                $envio = EnvioSolucionProblema::where('id_resolver', '=', $request->id_resolver)->where('id_cursa', '=', $request->id_cursa)->orderBy('created_at', 'DESC')->first();
            }

            // Si no existe un intento abierto o ya fue finalizado, creamos un nuevo registro de envío
            if(!$envio || $envio->termino != null){
                $envio = new EnvioSolucionProblema;
                $envio->token = \Illuminate\Support\Str::random(40);
                $envio->ip_origen = $request->ip();
                $envio->inicio = Carbon::now();
                $envio->id_resolver = $request->id_resolver;
                if (isset($request->id_certamen)) {
                    $envio->id_certamen = $request->id_certamen;
                } else {
                    $envio->id_cursa = $request->id_cursa;
                }
            }

            $envio->codigo = $request->codigo;
            $envio->ip_origen = $request->ip();
            $juezVirtual = JuecesVirtuales::find($request->juez_virtual) ?? JuecesVirtuales::first();
            if ($juezVirtual) {
                $envio->juez_virtual()->associate($juezVirtual);
            }

            $lenguaje = LenguajesProgramaciones::where("codigo", "=", $request->lenguaje)->first();
            if ($lenguaje) {
                $probPivot = $lenguaje->problemas()->find($request->id_problema);
                if ($probPivot && isset($probPivot->pivot)) {
                    $envio->ProblemaLenguaje()->disassociate();
                    $envio->ProblemaLenguaje()->associate($probPivot->pivot);
                } else {
                    $resolver = Resolver::where('id_problema', $request->id_problema)->where('id_lenguaje', $lenguaje->id)->first();
                    if ($resolver) {
                        $envio->id_resolver = $resolver->id;
                    }
                }
            }
            $envio->termino = Carbon::now();
            $envio->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Error en enviar_solucion: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Error al procesar el envío: ' . $e->getMessage())->with('codigo', $request->codigo);
        }
        
        try {
            $resultado = $this->enviar_api_juez($envio, $request->lenguaje);
            if ($resultado["estado"]) {            
                return redirect()->route('envios.ver', ["token" => $envio->token]);
            } else {
                return back()->with('error', $resultado["mensaje"])->with("codigo", $request->codigo);
            }
        } catch (\Throwable $e) {
            \Log::error("Error critico en evaluacion: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Error durante la evaluación de la solución: ' . $e->getMessage())->with("codigo", $request->codigo);
        }
    }

    public function enviar_api_juez($envio, $lenguaje)
    {
        $c_codes = [104, 75, 103, 48, 49, 50]; //codigos de compiladores de C en Judge0
        try {
            $problema = $envio->problema;
            if (!$problema && $envio->id_resolver) {
                $res = Resolver::with('problema')->find($envio->id_resolver);
                $problema = $res ? $res->problema : null;
            }
            if (!$problema) {
                return ["estado" => false, "mensaje" => "No se pudo encontrar el problema asociado al envío."];
            }

            $juez = $envio->juez_virtual ?? JuecesVirtuales::first();
            if (!$juez) {
                return ["estado" => false, "mensaje" => "No hay jueces virtuales configurados en la plataforma."];
            }

            $fuenteCodigo = $envio->codigo;

            // Detección de lenguaje SQL para evaluación especializada (vistas, triggers, funciones, consultas)
            $lenguajeObj = $envio->lenguaje;
            $esSql = false;
            if ($lenguajeObj) {
                $nombreLenguaje = strtolower($lenguajeObj->nombre ?? '');
                $abrevLenguaje = strtolower($lenguajeObj->abreviatura ?? '');
                if (str_contains($nombreLenguaje, 'sql') || str_contains($abrevLenguaje, 'sql')) {
                    $esSql = true;
                }
            }
            if (is_string($lenguaje) && str_contains(strtolower($lenguaje), 'sql')) {
                $esSql = true;
            }

            if ($esSql) {
                return app(\App\Services\SqlEvaluationService::class)->evaluar($envio);
            }

            if (isset($problema->archivo_adicional) && !str_contains($fuenteCodigo, '.open')) {
                $fuenteCodigo = ".open db.sqlite\n" . $fuenteCodigo;
            }
            $codigo = base64_encode($fuenteCodigo);

            //Transformación a JSON en batch del código fuente, los casos de prueba (entradas y salidas) y su memoria limite y tiempo limite.
            $batch_submissions = [];
            $casos = $problema->casos_de_prueba()->get();
            $initial_json_string = '{"language_id":' . $lenguaje . ',"source_code":"' . $codigo . '"';
            if(in_array($lenguaje, $c_codes)){
                //añade la opción de compilador -lm para incluir la libreria <math.h>, esto es solo para soluciones desarrolladas en C.
                $initial_json_string = $initial_json_string.',"compiler_options":"-lm"';
            }
            if(isset($problema->memoria_limite) && $problema->memoria_limite>0){
                $initial_json_string = $initial_json_string.',"memory_limit":"'.$problema->memoria_limite.'"';
            }
            if(isset($problema->tiempo_limite) && $problema->tiempo_limite>0){
                $initial_json_string = $initial_json_string.',"cpu_time_limit":"'.$problema->tiempo_limite.'"';
            }
            if(isset($problema->archivo_adicional)){
                $base64_encoded_file = base64_encode(Storage::get('public/archivos_adicionales/'.$problema->archivo_adicional));
                $initial_json_string = $initial_json_string.',"additional_files":"'.$base64_encoded_file.'"';
            }
            if(sizeof($casos)==0){
                $string_json = $initial_json_string.'}';
                array_push($batch_submissions, $string_json);
            }
            foreach ($casos as $caso) {
                $entrada = base64_encode($caso->entradas);
                $salida = base64_encode($caso->salidas);
                $string_json = $initial_json_string;
                if(isset($entrada)){
                    $string_json = $string_json.',"stdin":"'.$entrada.'"';
                }
                if(isset($salida)){
                    $string_json = $string_json.',"expected_output":"'.$salida.'"';
                }
                $string_json = $string_json.'}';
                array_push($batch_submissions, $string_json);
            }
            $client = new Client(['timeout' => 15.0]);
            //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
            $headerRequest = JuecesVirtuales::generateHeaderRequest($juez);
            $headerRequest['Content-Type'] = 'application/json';

            try {
                $chunks = array_chunk($batch_submissions, 20);
                $data = [];
                foreach ($chunks as $chunk) {
                    $response = $client->request('POST', $juez->direccion . '/submissions/batch?base64_encoded=true', [
                        'body' => '{"submissions":[' . implode(',', $chunk) . ']}',
                        'headers' => $headerRequest,
                    ]);
                    $chunkData = json_decode($response->getBody(), true);
                    if (is_array($chunkData)) {
                        $data = array_merge($data, $chunkData);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Fallo de conexion con Judge0 en " . $juez->direccion . ": " . $e->getMessage());

                // En entorno Windows o si se habilita fallback explícito, usar compilador local
                if (PHP_OS_FAMILY === 'Windows' || env('JUDGE0_FALLBACK_LOCAL', false)) {
                    return $this->simular_evaluacion_local($envio, $casos, $problema);
                }

                // En el servidor Linux, alertar de forma clara que Judge0 en Docker no está respondiendo
                $nombreLenguaje = $envio->lenguaje->nombre ?? 'este lenguaje';
                return [
                    "estado" => false,
                    "mensaje" => "No se pudo conectar con el servicio Judge0 ({$juez->direccion}) para {$nombreLenguaje}. Causa: " . $e->getMessage() . ". Asegúrese de iniciar los contenedores en el servidor con: 'cd /var/www/TutorBotPlus-2.0/judge0-v1.13.0 && sudo docker compose up -d'."
                ];
            }

            DB::beginTransaction();
            for ($i = 0; $i < sizeof($batch_submissions); $i++) {
                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = isset($data[$i]['token']) ? $data[$i]['token'] : \Illuminate\Support\Str::random(40);
                $evaluacion->envio()->associate($envio);
                if(sizeof($casos)>0 && isset($casos[$i])){
                    $evaluacion->casos_pruebas()->associate($casos[$i]);
                }
                $evaluacion->save();
            }
            DB::commit();
            return ["estado" => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Error general en enviar_api_juez: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ["estado" => false, "mensaje" => "Error durante el procesamiento del juez: " . $e->getMessage()];
        }
    }

    private function simular_evaluacion_local($envio, $casos, $problema)
    {
        $lenguajeObj = $envio->lenguaje;
        if ($lenguajeObj && (str_contains(strtolower($lenguajeObj->nombre ?? ''), 'sql') || str_contains(strtolower($lenguajeObj->abreviatura ?? ''), 'sql'))) {
            return app(\App\Services\SqlEvaluationService::class)->evaluar($envio);
        }

        $abreviatura = strtolower($lenguajeObj->abreviatura ?? '');
        $nombreLenguaje = strtolower($lenguajeObj->nombre ?? '');
        $isCpp = str_contains($abreviatura, 'cpp') || str_contains($abreviatura, 'c++') || str_contains($nombreLenguaje, 'c++');
        $isC = !$isCpp && (str_contains($abreviatura, 'c') || str_contains($nombreLenguaje, 'c'));

        if ($isC || $isCpp) {
            return $this->evaluar_c_local($envio, $casos, $problema, $isCpp);
        }

        if (str_contains($abreviatura, 'py') || str_contains($nombreLenguaje, 'python')) {
            return $this->evaluar_python_local($envio, $casos, $problema);
        }

        return [
            "estado" => false,
            "mensaje" => "La evaluación local no está disponible para " . ($lenguajeObj->nombre ?? 'este lenguaje') . ". Se requiere el servicio Judge0 (Docker) activo."
        ];
    }

    private function evaluar_c_local($envio, $casos, $problema, bool $isCpp = false)
    {
        $baseTemp = storage_path('app/temp');
        if (!file_exists($baseTemp)) {
            @mkdir($baseTemp, 0777, true);
        }
        $tempDir = $baseTemp . DIRECTORY_SEPARATOR . 'eval_c_' . $envio->id . '_' . time();
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $compiler = $isCpp ? 'C:\MinGW\bin\g++.exe' : 'C:\MinGW\bin\gcc.exe';
        if (!file_exists($compiler)) {
            $compiler = $isCpp ? 'g++' : 'gcc';
        }

        $sourceExt = $isCpp ? '.cpp' : '.c';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . 'solution' . $sourceExt;
        $exeFile = $tempDir . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'solution.exe' : 'solution.out');

        file_put_contents($sourceFile, $envio->codigo);

        $cmdCompile = escapeshellarg($compiler) . ' -O2 -o ' . escapeshellarg($exeFile) . ' ' . escapeshellarg($sourceFile) . ' -lm 2>&1';
        exec($cmdCompile, $compileOutputLines, $compileReturnCode);
        $compileOutput = implode("\n", $compileOutputLines);

        if (file_exists($exeFile)) {
            @chmod($exeFile, 0755);
        }

        $comparator = app(\App\Services\OutputComparatorService::class);
        $cantResueltos = 0;
        $puntajeTotal = 0;

        try {
            DB::beginTransaction();
            $envio->evaluaciones()->delete();

            if ($compileReturnCode !== 0 || !file_exists($exeFile)) {
                foreach ($casos as $caso) {
                    $evaluacion = new EvaluacionSolucion;
                    $evaluacion->token = \Illuminate\Support\Str::random(40);
                    $evaluacion->envio()->associate($envio);
                    $evaluacion->casos_pruebas()->associate($caso);
                    $evaluacion->estado = 'Error';
                    $evaluacion->resultado = 'Error de Compilación';
                    $evaluacion->error_compilacion = base64_encode($compileOutput);
                    $evaluacion->stout = base64_encode('');
                    $evaluacion->tiempo = '0.000';
                    $evaluacion->memoria = '0';
                    $evaluacion->save();
                }
            } else {
                foreach ($casos as $caso) {
                    $evaluacion = new EvaluacionSolucion;
                    $evaluacion->token = \Illuminate\Support\Str::random(40);
                    $evaluacion->envio()->associate($envio);
                    $evaluacion->casos_pruebas()->associate($caso);

                    $salidaEsperada = trim($caso->salidas ?? '');
                    $entrada = $caso->entradas ?? '';

                    $startTime = microtime(true);
                    $descriptorspec = [
                        0 => ["pipe", "r"],
                        1 => ["pipe", "w"],
                        2 => ["pipe", "w"],
                    ];

                    $process = proc_open(escapeshellarg($exeFile), $descriptorspec, $pipes, $tempDir);
                    $salidaObtenida = '';
                    $stderr = '';

                    if (is_resource($process)) {
                        if (!empty($entrada)) {
                            fwrite($pipes[0], $entrada);
                        }
                        fclose($pipes[0]);

                        $salidaObtenida = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);

                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[2]);

                        proc_close($process);
                    }

                    $tiempo = number_format(microtime(true) - $startTime, 4, '.', '');
                    $salidaObtenidaTrim = trim($salidaObtenida);

                    $exactMatch = ($salidaObtenidaTrim === $salidaEsperada);
                    $flexMatch = !$exactMatch && $comparator->isMatch($salidaObtenidaTrim, $salidaEsperada);
                    $isAccepted = $exactMatch || $flexMatch;

                    $evaluacion->estado = $isAccepted ? 'Aceptado' : 'Rechazado';
                    $evaluacion->resultado = $exactMatch ? 'Accepted' : ($flexMatch ? 'Aceptado (Variación de Formato)' : 'Wrong Answer');
                    $evaluacion->stout = base64_encode($salidaObtenida);
                    $evaluacion->tiempo = $tiempo;
                    $evaluacion->memoria = '1500';
                    $evaluacion->save();

                    if ($isAccepted) {
                        $cantResueltos++;
                        $puntajeTotal += ($caso->puntos ?? 0);
                    }
                }
            }

            $envio->cant_casos_resuelto = $cantResueltos;
            $envio->puntaje = $puntajeTotal;
            $envio->solucionado = ($cantResueltos === count($casos) && count($casos) > 0);
            $envio->save();
            DB::commit();

            if (!empty(trim($problema->restricciones ?? ''))) {
                try {
                    LlmController::ejecutar_verificacion_restricciones($envio);
                } catch (\Exception $e) {}
            }

            return ["estado" => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ["estado" => false, "mensaje" => "Error al evaluar localmente: " . $e->getMessage()];
        } finally {
            @unlink($sourceFile);
            @unlink($exeFile);
            if (is_dir($tempDir)) {
                @rmdir($tempDir);
            }
        }
    }

    private function evaluar_python_local($envio, $casos, $problema)
    {
        $baseTemp = storage_path('app/temp');
        if (!file_exists($baseTemp)) {
            @mkdir($baseTemp, 0777, true);
        }
        $tempDir = $baseTemp . DIRECTORY_SEPARATOR . 'eval_py_' . $envio->id . '_' . time();
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.py';
        file_put_contents($sourceFile, $envio->codigo);

        $pythonCmd = 'python';
        if (PHP_OS_FAMILY !== 'Windows') {
            $whichPython3 = trim(shell_exec('which python3 2>/dev/null') ?? '');
            $whichPython = trim(shell_exec('which python 2>/dev/null') ?? '');
            $pythonCmd = !empty($whichPython3) ? $whichPython3 : (!empty($whichPython) ? $whichPython : 'python3');
        }
        $comparator = app(\App\Services\OutputComparatorService::class);
        $cantResueltos = 0;
        $puntajeTotal = 0;

        try {
            DB::beginTransaction();
            $envio->evaluaciones()->delete();

            foreach ($casos as $caso) {
                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = \Illuminate\Support\Str::random(40);
                $evaluacion->envio()->associate($envio);
                $evaluacion->casos_pruebas()->associate($caso);

                $salidaEsperada = trim($caso->salidas ?? '');
                $entrada = $caso->entradas ?? '';

                $startTime = microtime(true);
                $descriptorspec = [
                    0 => ["pipe", "r"],
                    1 => ["pipe", "w"],
                    2 => ["pipe", "w"],
                ];

                $process = proc_open("$pythonCmd " . escapeshellarg($sourceFile), $descriptorspec, $pipes, $tempDir);
                $salidaObtenida = '';
                $stderr = '';

                if (is_resource($process)) {
                    if (!empty($entrada)) {
                        fwrite($pipes[0], $entrada);
                    }
                    fclose($pipes[0]);

                    $salidaObtenida = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);

                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);

                    proc_close($process);
                }

                $tiempo = number_format(microtime(true) - $startTime, 4, '.', '');

                if (!empty($stderr) && empty($salidaObtenida)) {
                    $evaluacion->estado = 'Error';
                    $evaluacion->resultado = 'Runtime Error';
                    $evaluacion->error_compilacion = base64_encode($stderr);
                    $evaluacion->stout = base64_encode('');
                } else {
                    $salidaObtenidaTrim = trim($salidaObtenida);
                    $exactMatch = ($salidaObtenidaTrim === $salidaEsperada);
                    $flexMatch = !$exactMatch && $comparator->isMatch($salidaObtenidaTrim, $salidaEsperada);
                    $isAccepted = $exactMatch || $flexMatch;

                    $evaluacion->estado = $isAccepted ? 'Aceptado' : 'Rechazado';
                    $evaluacion->resultado = $exactMatch ? 'Accepted' : ($flexMatch ? 'Aceptado (Variación de Formato)' : 'Wrong Answer');
                    $evaluacion->stout = base64_encode($salidaObtenida);

                    if ($isAccepted) {
                        $cantResueltos++;
                        $puntajeTotal += ($caso->puntos ?? 0);
                    }
                }

                $evaluacion->tiempo = $tiempo;
                $evaluacion->memoria = '1500';
                $evaluacion->save();
            }

            $envio->cant_casos_resuelto = $cantResueltos;
            $envio->puntaje = $puntajeTotal;
            $envio->solucionado = ($cantResueltos === count($casos) && count($casos) > 0);
            $envio->save();
            DB::commit();

            if (!empty(trim($problema->restricciones ?? ''))) {
                try {
                    LlmController::ejecutar_verificacion_restricciones($envio);
                } catch (\Exception $e) {}
            }

            return ["estado" => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ["estado" => false, "mensaje" => "Error al evaluar localmente: " . $e->getMessage()];
        } finally {
            @unlink($sourceFile);
            if (is_dir($tempDir)) {
                @rmdir($tempDir);
            }
        }
    }
}
