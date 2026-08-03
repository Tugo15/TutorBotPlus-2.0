<?php

namespace App\Models;

use App\Jobs\EnviosProcess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\User;
use App\Models\JuecesVirtuales;
use App\Models\EvaluacionSolucion;
use App\Models\Problemas;
use App\Models\Cursos;
use App\Models\Cursa;
use App\Models\LenguajesProgramaciones;
use App\Models\SolicitudRaLlm;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnvioSolucionProblema extends Model
{
    use HasFactory;

    protected $fillable = [
            'token',
            'id_problema',
            'id_usuario', 
            'id_curso',
            'id_juez', 
            'id_lenguaje',
            'codigo',
            'inicio',
            'termino',
            'cant_casos_resueltos',
            'puntaje',
            'solucionado',
    ];
    
    public static $rules = [
        "codigo" => ["required", "min:5"],
    ];

    public static $higlightjs_language = [
        "py" => "python",
        "c++" => "cpp",
        "c" => "c",
        "java" => "java",
        "sql" => "sql",
    ];

    public function CursoUsuario(): BelongsTo
    {
        return $this->BelongsTo(Cursa::class, 'id_cursa');
    }

    public function juez_virtual(): BelongsTo
    {
        return $this->belongsTo(JuecesVirtuales::class, 'id_juez');
    }
    public function usuario(): HasOneThrough{
        return $this->hasOneThrough(User::class, Cursa::class, 'id', 'id','id_cursa', 'id_usuario');
    }
    public function curso(): HasOneThrough{
        return $this->hasOneThrough(Cursos::class, Cursa::class, 'id', 'id','id_cursa', 'id_curso');
    }
    public function lenguaje(): HasOneThrough{
        return $this->hasOneThrough(LenguajesProgramaciones::class, Resolver::class, 'id', 'id','id_resolver', 'id_lenguaje');
    }
    public function problema(): HasOneThrough{
        return $this->hasOneThrough(Problemas::class, Resolver::class, 'id', 'id','id_resolver', 'id_problema');
    }
    public function ProblemaLenguaje(): BelongsTo
    {
        return $this->BelongsTo(Resolver::class, 'id_resolver');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(EvaluacionSolucion::class, 'id_envio');
    }

    public function retroalimentaciones(): HasMany
    {
        return $this->hasMany(SolicitudRaLlm::class,'id_envio');
    }
}
