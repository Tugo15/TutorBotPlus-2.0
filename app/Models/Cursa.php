<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\User;
use App\Models\Cursos;
use App\Models\EnvioSolucionProblema;
class Cursa extends Pivot
{
    protected $table = "cursa";

    public function curso(): BelongsTo{
        return $this->belongsTo(Cursos::class, 'id_curso');
    }

    public function usuario(): BelongsTo{
        return $this->belongsTo(User::class, 'id_user');
    }

    public function envios(): HasMany{
        return $this->hasMany(EnvioSolucionProblema::class, 'id_cursa');
    }
}
