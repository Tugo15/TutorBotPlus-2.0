<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

        foreach ($envios as $envio) {
            $ok = self::evaluarSingleEnvio($envio);
            if (!$ok) {
                $this->warn("No se pudo procesar o ya está en proceso el envío #{$envio->id}");
            }
        }

        $this->info("Se ha verificado las evaluaciones pendientes (".sizeof($envios).")");
    }

    /**
     * Evalúa de forma atómica un único envío pendiente consultando Judge0 por lote.
     */
    public static function evaluarSingleEnvio(EnvioSolucionProblema $envio): bool
    {
        $lock = Cache::lock('evaluar_envio_' . $envio->id, 15);
        if (!$lock->get()) {
            return true;
        }

        try {
            $problema = $envio->problema;
            $cant_evaluaciones = $envio->evaluaciones()->count();
            $evaluacion_arr = [];
            $evaluaciones = $envio->evaluaciones()->where('estado', '=', 'En Proceso')->get();

            if ($evaluaciones->isEmpty()) {
                return true;
            }

            foreach ($evaluaciones as $evaluacion) {
                $evaluacion_arr[$evaluacion->token] = $evaluacion;
            }

            $juez = $envio->juez_virtual;
            if (!$juez) {
                return false;
            }

            $client = new Client(['timeout' => 10.0]);
            $header = JuecesVirtuales::generateHeaderRequest($juez);
            $tokenChunks = array_chunk(array_keys($evaluacion_arr), 20);
            $submissions = [];

            foreach ($tokenChunks as $tokens) {
                $response = $client->request('GET', $juez->direccion . '/submissions/batch?tokens=' . implode('%2C', $tokens) . '&base64_encoded=true&fields=*', [
                    'headers' => $header,
                ]);
                $resData = json_decode($response->getBody(), true);
                if (isset($resData["submissions"]) && is_array($resData["submissions"])) {
                    $submissions = array_merge($submissions, $resData["submissions"]);
                }
            }
            $data = ["submissions" => $submissions];

            DB::beginTransaction();
            $comparator = app(\App\Services\OutputComparatorService::class);

            foreach ($data["submissions"] as $index => $item) {
                if (is_null($item)) {
                    $tokensKeys = array_keys($evaluacion_arr);
                    if (isset($tokensKeys[$index]) && isset($evaluacion_arr[$tokensKeys[$index]])) {
                        $evaluacionHuerfana = $evaluacion_arr[$tokensKeys[$index]];
                        $evaluacionHuerfana->estado = "Error";
                        $evaluacionHuerfana->resultado = "Token No Encontrado";
                        $evaluacionHuerfana->error_compilacion = "El token de evaluación expiró o pertenecía a una instancia previa del juez.";
                        $evaluacionHuerfana->save();
                    }
                    continue;
                }

                if (!isset($item["token"]) || !isset($evaluacion_arr[$item["token"]])) {
                    continue;
                }

                $evaluacion = $evaluacion_arr[$item["token"]];
                $evaluacion->resultado = \App\Support\Judge0StatusHelper::traducir($item['status']["description"] ?? 'Error');

                if (isset($item['status']["id"]) && $item['status']["id"] != 1 && $item['status']["id"] != 2) {
                    $evaluacion->tiempo = $item['time'] ?? null;
                    $evaluacion->memoria = $item['memory'] ?? null;

                    $stdout = $item['stdout'] ?? '';
                    if (strlen($stdout) <= 65535) {
                        $evaluacion->stout = $stdout;
                    } else {
                        $evaluacion->stout = base64_encode("Error: El texto de salida es muy largo.");
                    }

                    if (isset($item["stderr"])) {
                        $evaluacion->error_compilacion = $item["stderr"];
                    } else {
                        $evaluacion->error_compilacion = $item["compile_output"] ?? null;
                    }

                    $isAccepted = ($item['status']["id"] == 3);
                    if (!$isAccepted && $item['status']["id"] == 4 && isset($evaluacion->casos_pruebas)) {
                        $actualOutput = base64_decode($stdout) ?: $stdout;
                        $expectedOutput = $evaluacion->casos_pruebas->salidas;

                        if ($comparator->isMatch($actualOutput, $expectedOutput)) {
                            $isAccepted = true;
                            $evaluacion->resultado = "Aceptado (Variación de Formato)";
                        }
                    }

                    if ($isAccepted) {
                        $evaluacion->estado = "Aceptado";
                        $envio->cant_casos_resuelto = $envio->cant_casos_resuelto + 1;
                        if (isset($evaluacion->casos_pruebas->puntos)) {
                            $envio->puntaje = $envio->puntaje + $evaluacion->casos_pruebas->puntos;
                        }
                    } else if ($item['status']["id"] == 4) {
                        $evaluacion->estado = "Rechazado";
                    } else if ($item['status']["id"] >= 5) {
                        $evaluacion->estado = "Error";
                    }
                    $evaluacion->save();
                }
            }

            if ($cant_evaluaciones == $envio->cant_casos_resuelto && $envio->solucionado == false) {
                $envio->solucionado = true;
                if (!isset($envio->id_certamen) && $envio->curso && $problema) {
                    $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
                    DB::table('disponible')->where('id_curso', '=', $envio->curso->id)->where('id_problema', '=', $problema->id)->incrementEach([
                        "cantidad_resueltos" => 1,
                        "tiempo_total" => $diferencia,
                    ]);
                }
            }
            $envio->save();
            DB::commit();

            return true;
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error("Error al procesar evaluación del envío #{$envio->id}: " . $e->getMessage());
            return false;
        } finally {
            $lock->release();
        }
    }
}
