<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ResolucionCertamenes;
use Carbon\Carbon;

class FinalizarCertamenesExpirados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalizar-certamenes-expirados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza automáticamente las resoluciones de certámenes cuyo tiempo límite efectivo haya expirado.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $resolucionesAbiertas = ResolucionCertamenes::with('certamen')
            ->where('finalizado', false)
            ->get();

        $finalizados = 0;
        foreach ($resolucionesAbiertas as $resolucion) {
            if (!$resolucion->certamen) {
                continue;
            }
            $limite = $resolucion->fecha_termino_efectiva ?? Carbon::parse($resolucion->certamen->fecha_termino);
            if ($now->gt($limite)) {
                try {
                    $resolucion->finalizar_certamen();
                    $finalizados++;
                } catch (\Throwable $e) {
                    // Continuar con las demás resoluciones
                }
            }
        }

        $this->info("Resoluciones expiradas finalizadas: {$finalizados}");
        return Command::SUCCESS;
    }
}

