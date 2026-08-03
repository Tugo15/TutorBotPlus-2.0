<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JuecesVirtuales;
use App\Models\EnvioSolucionProblema;
class EvaluarEnvios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:evaluar-envios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite obtener las evaluaciones de los envíos pendientes de revisión';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envios = EnvioSolucionProblema::whereHas('evaluaciones', function (Builder $query) {
            $query->where('estado', '=', 'En Proceso');
        })->get();
        foreach($envios as $envio){
            $problema = $envio->problema;
            $cant_evaluaciones = $envio->evaluaciones()->count();
            $evaluacion_arr = [];
            $evaluaciones = $envio->evaluaciones()->where('estado', '=', 'En Proceso')->get();
            foreach($evaluaciones as $evaluacion){
                $evaluacion_arr[$evaluacion->token] = $evaluacion;
            }
            $juez = $envio->juez_virtual;
            $client = new Client();
            //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
            $header = JuecesVirtuales::generateHeaderRequest($juez);
            try {
                $response = $client->request('GET', $juez->direccion . '/submissions/batch?tokens=' . implode('%2C', array_keys($evaluacion_arr)) . '&base64_encoded=true&fields=*', [
                    'headers' => $header,
                ]);
                $data = json_decode($response->getBody(), true);
            }catch(\Exception $e){
                continue;
            }
            try{
                DB::beginTransaction();
                foreach ($data["submissions"] as $item) {
                    $evaluacion = $evaluacion_arr[$item["token"]];
                    $evaluacion->resultado = $item['status']["description"];
                    if ($item['status']["id"] != 1 && $item['status']["id"] != 2) {
                        $evaluacion->tiempo = $item['time'];
                        $evaluacion->memoria = $item['memory'];
                        //Previene almacenar salidas muy largas debido a loops infinito en códigos que imprimen de manera infinita strings
                        if(strlen($item['stdout'])<=65535){
                            $evaluacion->stout = $item['stdout'];
                        }else{
                            $evaluacion->stout = base64_encode("Error: El texto de salida es muy largo.");
                        }
                        if (isset($item["stderr"])) {
                            $evaluacion->error_compilacion = $item["stderr"];
                        } else {
                            $evaluacion->error_compilacion = $item["compile_output"];
                        }
                        if ($item['status']["id"] == 3) {
                            $evaluacion->estado = "Aceptado";
                            $envio->cant_casos_resuelto = $envio->cant_casos_resuelto + 1;
                            if(isset($evaluacion->casos_pruebas->puntos)){
                                $envio->puntaje = $envio->puntaje + $evaluacion->casos_pruebas->puntos;  
                            }
                        } else if ($item['status']["id"] == 4) {
                            $evaluacion->estado = "Rechazado";
                        } else if ($item['status']["id"] >= 5 && $item['status']["id"] <= 12) {
                            $evaluacion->estado = "Error";
                        }
                        $evaluacion->save();
                    }
                }
                if ($cant_evaluaciones == $envio->cant_casos_resuelto && $envio->solucionado == false) {
                    $envio->solucionado = true;
                    if(!isset($envio->id_certamen)){
                        $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
                        DB::table('disponible')->where('id_curso', '=', $envio->curso->id)->where('id_problema', '=', $problema->id)->incrementEach(
                            ["cantidad_resueltos"=>1,
                            "tiempo_total"=>$diferencia,
                            ]
                        );
                    }
                }
                $envio->save();
                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
                $this->error("Error en la conexión con la base de datos");
            }
        }
        $this->info("Se ha verificado las evaluaciones pendientes (".sizeof($envios).")");
    }
}
