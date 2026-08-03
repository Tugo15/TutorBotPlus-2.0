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
        ->select('resultado', 'id_envio', 'estado')
        ->where('estado', '=', 'Rechazado')
        ->orWhere('estado', '=', 'En Proceso')
        ->orWhere('estado', '=', 'Error')
        ->orderBy('id_caso','DESC')
        ->groupBy('id_envio', 'resultado', 'estado');


        //dd($ultima_evaluacion->get());
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
        ->select('problemas.nombre as nombre_problema', 'problemas.codigo as codigo_problema','envio_solucion_problemas.id as id_envio', 'envio_solucion_problemas.created_at','envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje', 'lenguajes_programaciones.nombre as nombre_lenguaje', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'cursos.nombre as nombre_curso', 'cursos.id as id_curso', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado', DB::raw('count(casos__pruebas.id) as total_casos'))
        ->groupBy('problemas.nombre', 'problemas.codigo','envio_solucion_problemas.id', 'envio_solucion_problemas.created_at','envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje', 'lenguajes_programaciones.nombre', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'cursos.nombre', 'cursos.id', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado')
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
            $codigo = base64_encode($envio->codigo);
        } catch (\PDOException $e) {
            return ["estado" => false];
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
        $client = new Client();
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
            return ["estado" => false, "mensaje" => $e->getMessage()];
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
}
