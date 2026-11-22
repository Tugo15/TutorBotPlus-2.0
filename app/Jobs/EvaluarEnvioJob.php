<?php

namespace App\Jobs;

use App\Console\Commands\EvaluarEnvios;
use App\Models\EnvioSolucionProblema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluarEnvioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3;

    public function __construct(public int $envioId)
    {
    }

    public function handle(): void
    {
        $envio = EnvioSolucionProblema::find($this->envioId);
        if (!$envio) {
            return;
        }

        EvaluarEnvios::evaluarSingleEnvio($envio);
    }
}
