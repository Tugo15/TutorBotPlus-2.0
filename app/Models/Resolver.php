<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Resolver extends Pivot
{
    protected $table = "resolver";
    protected $primaryKey = 'id';
    public $incrementing = true;
    public function problema(): BelongsTo{
        return $this->belongsTo(Problemas::class, 'id_problema');
    }

    public function lenguaje(): BelongsTo{
        return $this->belongsTo(LenguajesProgramaciones::class, 'id_lenguaje');
    }

    public function envios(): HasMany{
        return $this->hasMany(EnvioSolucionProblema::class, 'id_resolver');
    }
}
