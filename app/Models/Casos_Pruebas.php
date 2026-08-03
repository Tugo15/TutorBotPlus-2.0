<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Problemas;
class Casos_Pruebas extends Model
{
    use HasFactory;

    protected $fillable = [
        "entradas",
        "salidas",
        "puntos"
    ];

    public static $rules = [
        "entradas" => ["nullable","string"],
        "salidas" => ["required","string"],
        "puntos" => ["nullable","numeric"],
    ];
    public function problema(): BelongsTo
    {
        return $this->belongsTo(Problemas::class, 'id_problema');
    }
}
