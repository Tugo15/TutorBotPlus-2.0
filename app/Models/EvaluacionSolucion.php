<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Casos_Pruebas;
use App\Models\EnvioSolucionProblema;
class EvaluacionSolucion extends Model
{
    use HasFactory;

    public function casos_pruebas(): BelongsTo
    {
        return $this->belongsTo(Casos_Pruebas::class, 'id_caso');
    }
    
    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioSolucionProblema::class,'id_envio');
    }
}
