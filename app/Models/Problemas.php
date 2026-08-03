<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Categoria_Problema;
use App\Models\LenguajesProgramaciones;
use App\Models\EnvioSolucionProblema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Problemas extends Model
{
    use HasFactory;
    

    protected $filable = [
        "nombre",
        "codigo",
        "fecha_inicio",
        "fecha_termino",
        "memoria_limite",
        "tiempo_limite",
        "visible",
        "body_problema",
        "body_problema_resumido",
        "habilitar_llm",
        "limite_llm",
        "archivo_adicional"
    ];

    public static $createRules = [
        "nombre" => ['required','max:255','string'],
        "codigo" => ['required', 'string', 'max:100', 'unique:App\Models\Problemas,codigo'], 
        "fecha_inicio" => ['date', 'nullable', 'before_or_equal:fecha_termino'],
        "fecha_termino" => ['date', 'nullable', 'after_or_equal:fecha_inicio'],
        "memoria_limite" => ["nullable","numeric"],
        "tiempo_limite" => ["nullable", "numeric"],
        "visible" => ["nullable", "boolean"],
        "body_problema" => ["required", "string", "min:50"],
        "body_problema_resumido" => ["string", 'nullable'],
        "habilitar_llm" => ["boolean"],
        "limite_llm" => ["nullable","numeric"],
        "archivo_adicional" => ["mimes:zip"]
    ];
    public static function createRules(bool $boolFechaInicio, bool $boolFechaTermino, $codigo=null, $sql=false, $update=false){
        $rules = [
            "nombre" => ['required','max:255','string'],
            "memoria_limite" => ["nullable","numeric", "min:2048"],
            "cursos" => ["required","array","min:1"],
            "tiempo_limite" => ["nullable", "numeric", "gt:0"],
            "visible" => ["nullable", "boolean"],
            "body_problema" => ["required", "string", "min:50"],
            "body_problema_resumido" => ["string", 'nullable'],
            "habilitar_llm" => ["boolean"],
            "limite_llm" => ["nullable","numeric", "min:0"],
        ];
        if(!$sql){
            $rules["lenguajes"] = ['required', 'array', 'min:1'];
        }else if($sql && !$update){
            $rules["archivos_adicionales"] = ['required', 'mimes:zip'];
        }
        
        if($boolFechaInicio && $boolFechaTermino){
            $rules["fecha_inicio"] = ['date', 'nullable', 'before_or_equal:fecha_termino'];
            $rules["fecha_termino"] = ['date', 'nullable', 'after_or_equal:fecha_inicio'];

        }else{
            $rules["fecha_inicio"] = ['date', 'nullable'];
            $rules["fecha_termino"] = ['date', 'nullable'];
        }

        if(isset($codigo)){
            $rules["codigo"] = ['required', 'string', 'max:100', Rule::unique('problemas')->ignore($codigo, "codigo")]; 
        }else{
            $rules["codigo"] = ['required', 'string', 'max:100', Rule::unique('problemas')];
        }
        return $rules;
    }
    public static $llm_config_rules = [
        "habilitar_llm" => ["boolean"],
        "limite_llm" => ["nullable","numeric"],
        "body_problema_resumido" => ["string", 'nullable'],
    ];
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria_Problema::class, 'pertenece', 'id_problema', 'id_categoria');
    }

    public function lenguajes(): BelongsToMany
    {
        return $this->belongsToMany(LenguajesProgramaciones::class, 'resolver', 'id_problema', 'id_lenguaje')->withTimestamps()->withPivot('id')->using(Resolver::class);
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Cursos::class,'disponible','id_problema', 'id_curso');  
    }

    public function casos_de_prueba(): HasMany
    {
        return $this->hasMany(Casos_Pruebas::class, 'id_problema');
    }
    
    public function envios(): HasManyThrough
    {
        return $this->HasManyThrough(EnvioSolucionProblema::class,Resolver::class, 'id_problema', 'id_resolver');
    }
}
