<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Problemas;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Categoria_Problema extends Model
{
    use HasFactory;

    protected $fillable = [
        "nombre"
    ];

    public static $rules = [
        "nombre" =>["required","string","max:255"],
    ];
    public function problemas(): BelongsToMany
    {
        return $this->belongsToMany(Problemas::class, 'pertenece', 'id_categoria', 'id_problema');
    }
}
