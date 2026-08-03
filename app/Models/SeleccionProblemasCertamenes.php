<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeleccionProblemasCertamenes extends Model
{
    use HasFactory;

    protected $table = "seleccion_problemas_certamen";


    public function problema(): BelongsTo{
        return $this->belongsTo(Problemas::class, 'id_problema');
    }

    public function resolucion(): BelongsTo{
        return $this->belongsTo(ResolucionCertamenes::class, 'id_res_certamen');
    }
}
