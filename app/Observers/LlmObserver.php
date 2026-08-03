<?php

namespace App\Observers;

use App\Models\SolicitudRaLlm;
use Illuminate\Support\Facades\DB;
class LlmObserver
{
    /**
     * Handle the SolicitudRaLlm "created" event.
     */
    public function created(SolicitudRaLlm $solicitudRaLlm): void
    {
        DB::table('disponible')
        ->where('id_curso', '=', $solicitudRaLlm->envio->curso->id)
        ->where('id_problema', '=', $solicitudRaLlm->envio->problema->id)
        ->increment('cant_retroalimentacion_solicitada');
    }

    /**
     * Handle the SolicitudRaLlm "updated" event.
     */
    public function updated(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }

    /**
     * Handle the SolicitudRaLlm "deleted" event.
     */
    public function deleted(SolicitudRaLlm $solicitudRaLlm): void
    {
        DB::table('disponible')
        ->where('id_curso', '=', $solicitudRaLlm->envio()->id_curso)
        ->where('id_problema', '=', $solicitudRaLlm->envio()->id_problema)
        ->decrement('cant_retroalimentacion_solicitada');
    }

    /**
     * Handle the SolicitudRaLlm "restored" event.
     */
    public function restored(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }

    /**
     * Handle the SolicitudRaLlm "force deleted" event.
     */
    public function forceDeleted(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }
}
