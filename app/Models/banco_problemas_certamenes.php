<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
class banco_problemas_certamenes extends Pivot
{   
    protected $table = 'banco_problemas_certamenes';
    protected $fillable = [
        "id_certamen",
        "id_categoria",
    ];

    public function certamen(): BelongsTo{
        return $this->belongsTo(Certamenes::class, 'id_certamen');
    }

    public function categorias(): HasMany{
        return $this->hasMany(Categoria_Problema::class, 'id_categoria');
    }

    protected static function booted()
    {
        static::created(function ($banco) {
            $certamen = $banco->certamen;
            $certamen->cantidad_problemas += 1;
            $certamen->save(); 
        });

        static::deleting(function ($banco){
            $certamen = $banco->certamen;
            $certamen->cantidad_problemas -= 1;
            $certamen->save(); 
        });
    }
}
