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

    public function getErrorCompilacionTextoAttribute(): string
    {
        return self::decodificarTextoSeguro($this->error_compilacion);
    }

    public function getSalidaTextoAttribute(): string
    {
        return self::decodificarTextoSeguro($this->stout);
    }

    public static function decodificarTextoSeguro(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $actual = $valor;
        for ($i = 0; $i < 2; $i++) {
            $limpio = trim($actual);
            $sinEspacios = preg_replace('/\s+/', '', $limpio);
            
            if (!empty($sinEspacios) && preg_match('/^[a-zA-Z0-9\+\/]+={0,2}$/', $sinEspacios) && (strlen($sinEspacios) % 4 === 0)) {
                $decodificado = base64_decode($sinEspacios, true);
                if ($decodificado !== false && mb_check_encoding($decodificado, 'UTF-8') && preg_match('/[\x20-\x7E\t\r\n\p{L}]/u', $decodificado)) {
                    $actual = $decodificado;
                    continue;
                }
            }
            break;
        }

        return $actual;
    }
}
