<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use App\Models\Problemas;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class LenguajesProgramaciones extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'abreviatura',
        'extension',
        'codigo',
    ];
    public static $createRules = [
        'nombre' => ['required', 'string', 'max:255'],
        'codigo' => ['required', 'integer', 'unique:App\Models\LenguajesProgramaciones,codigo'],
        'abreviatura' => ['required', 'string', 'max:255'],
        'extension' => ['required', 'string', 'max:15'], 
    ];

    public static function updateRules($id){
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'integer', Rule::unique('lenguajes_programaciones')->ignore($id)],
            'abreviatura' => ['required', 'string', 'max:255'],
            'extension' => ['required', 'string', 'max:15'], 
        ];
    }

    public function problemas(): BelongsToMany
    {
        return $this->belongsToMany(Problemas::class, 'resolver', 'id_lenguaje', 'id_problema')->withTimestamps()->withPivot(['id'])->using(Resolver::class);
    }
}
