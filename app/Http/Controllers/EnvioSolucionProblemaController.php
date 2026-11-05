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
        if ($request->juez_virtual == "0") {
            $request->juez_virtual = JuecesVirtuales::inRandomOrder()->first()->id;
        }
        try {
            DB::beginTransaction();
            if(isset($request->id_certamen)){
                $envio = EnvioSolucionProblema::where('id_resolver', '=', $request->id_resolver)->where('id_certamen', '=', $request->id_certamen)->orderBy('created_at', 'DESC')->first();
            }else{
                $envio = EnvioSolucionProblema::where('id_resolver', '=', $request->id_resolver)->where('id_cursa', '=', $request->id_cursa)->orderBy('created_at', 'DESC')->first();
            }
            if($envio->termino != null){
                return redirect()->route('envios.ver', ["token" => $envio->token]);
            }
            $envio->codigo = $request->codigo;
            $envio->ip_origen = $request->ip();
            $envio->juez_virtual()->associate(JuecesVirtuales::find($request->juez_virtual));
            $lenguaje = LenguajesProgramaciones::where("codigo", "=", $request->lenguaje)->first();
            $pivot_problema_lenguaje = $lenguaje->problemas()->find($request->id_problema)->pivot;
            $envio->ProblemaLenguaje()->disassociate();
            $envio->ProblemaLenguaje()->associate($pivot_problema_lenguaje);
            $envio->termino = Carbon::now();
            $envio->save();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->with('codigo', $request->codigo);
        }
        
        $resultado = $this->enviar_api_juez($envio, $request->lenguaje);
        if ($resultado["estado"]) {            
            return redirect()->route('envios.ver', ["token" => $envio->token]);
        } else {
            return back()->with('error', $resultado["mensaje"])->with("codigo", $request->codigo);
        }
    }

    public function enviar_api_juez($envio, $lenguaje)
    {
        $c_codes = [104, 75, 103, 48, 49, 50]; //codigos de compiladores de C en Judge0
        try {
            $problema = $envio->problema;
            $juez = $envio->juez_virtual;
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
        } catch (\PDOException $e) {
            return ["estado" => false, "mensaje" => $e->getMessage()];
        }
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
        $client = new Client(['timeout' => 4.0]);
        //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
        $headerRequest = JuecesVirtuales::generateHeaderRequest($juez);
        $headerRequest['Content-Type'] = 'application/json';
        try {
            $response = $client->request('POST', $juez->direccion . '/submissions/batch?base64_encoded=true', [
                'body' => '{"submissions":[' . implode(',', $batch_submissions) . ']}',
                'headers' => $headerRequest,
            ]);

            $data = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            if (app()->environment('local') || str_contains($juez->direccion, '127.0.0.1') || str_contains($juez->direccion, 'localhost')) {
                return $this->simular_evaluacion_local($envio, $casos, $problema);
            }
            $nombreLenguaje = $envio->lenguaje->nombre ?? 'este lenguaje';
            return [
                "estado" => false,
                "mensaje" => "No se pudo conectar al juez virtual Judge0 (" . $juez->direccion . ") para compilar y evaluar soluciones en " . $nombreLenguaje . ". Verifique que el contenedor de Judge0 (Docker) esté en ejecución."
            ];
        }
        try {
            DB::beginTransaction();
            for ($i = 0; $i < sizeof($batch_submissions); $i++) {
                
                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = $data[$i]['token'];
                $evaluacion->envio()->associate($envio);
                if(sizeof($casos)>0){
                    $evaluacion->casos_pruebas()->associate($casos[$i]);
                }
                $evaluacion->save();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return ["estado" => false, "mensaje" => "Error en el ingreso de evaluaciones a la base de datos"];
        }
        return ["estado" => true];
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
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eval_c_' . $envio->id . '_' . time();
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $compiler = $isCpp ? 'C:\MinGW\bin\g++.exe' : 'C:\MinGW\bin\gcc.exe';
        if (!file_exists($compiler)) {
            $compiler = $isCpp ? 'g++' : 'gcc';
        }

        $sourceExt = $isCpp ? '.cpp' : '.c';
        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . 'solution' . $sourceExt;
        $exeFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.exe';

        file_put_contents($sourceFile, $envio->codigo);

        $cmdCompile = escapeshellarg($compiler) . ' -O2 -o ' . escapeshellarg($exeFile) . ' ' . escapeshellarg($sourceFile) . ' -lm 2>&1';
        exec($cmdCompile, $compileOutputLines, $compileReturnCode);
        $compileOutput = implode("\n", $compileOutputLines);

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
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eval_py_' . $envio->id . '_' . time();
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $sourceFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.py';
        file_put_contents($sourceFile, $envio->codigo);

        $pythonCmd = 'python';
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
