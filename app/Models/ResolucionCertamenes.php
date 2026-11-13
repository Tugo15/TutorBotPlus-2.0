<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolucionCertamenes extends Model
{
    use HasFactory;

    public function finalizar_certamen()
    {
        if ($this->finalizado == false) {
            try {
                DB::beginTransaction();
                $this->finalizado = true;
                $certamen = $this->certamen;
                if (!$certamen) {
                    $this->save();
                    DB::commit();
                    return;
                }
                $_now = Carbon::now();
                if ($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino))) {
                    $this->fecha_finalizado = $_now;
                } else {
                    $this->fecha_finalizado = $certamen->fecha_termino;
                }
                $envios = $this->envios()->with('problema')->whereNotNull('termino')->orderBy('solucionado', 'asc')->get();
                $this->puntaje_obtenido = 0;
                $this->problemas_resueltos = 0;

                $enviosPorProblema = [];
                foreach ($envios as $envio) {
                    $probId = $envio->problema->id ?? optional($envio->ProblemaLenguaje)->id_problema;
                    if ($probId) {
                        $enviosPorProblema[$probId][] = $envio;
                    }
                }

                $pen_error = $certamen->penalizacion_error ?? 0;
                $cant_pen = $certamen->cantidad_penalizacion ?? 0;

                foreach ($enviosPorProblema as $probId => $listaEnvios) {
                    $resueltoProb = false;
                    $maxPuntajeProb = 0;
                    $erroresProb = 0;

                    foreach ($listaEnvios as $env) {
                        if (($env->puntaje ?? 0) > $maxPuntajeProb) {
                            $maxPuntajeProb = (int)$env->puntaje;
                        }
                        if ($env->solucionado == true) {
                            $resueltoProb = true;
                        } else {
                            $erroresProb++;
                        }
                    }

                    if ($resueltoProb) {
                        $this->problemas_resueltos += 1;
                    }

                    $erroresAplicados = min($erroresProb, $cant_pen);
                    $puntajeNeto = max(0, $maxPuntajeProb - ($erroresAplicados * $pen_error));
                    $this->puntaje_obtenido += $puntajeNeto;
                }
                $this->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
    }

    public function reabrir_certamen(int $minutosAdicionales = 0, ?string $motivo = null, ?int $userId = null): void
    {
        try {
            DB::beginTransaction();
            $this->finalizado = false;
            $this->fecha_finalizado = null;

            $certamen = $this->certamen;
            $ahora = Carbon::now();

            if ($certamen) {
                $fechaBase = Carbon::parse($certamen->fecha_termino);
                if ($minutosAdicionales > 0) {
                    if ($ahora->gt($fechaBase)) {
                        $nuevaFecha = $ahora->copy()->addMinutes($minutosAdicionales);
                    } else {
                        $nuevaFecha = $fechaBase->copy()->addMinutes($minutosAdicionales);
                    }
                } elseif ($ahora->gte($fechaBase)) {
                    $nuevaFecha = $ahora->copy()->addMinutes(15);
                } else {
                    $nuevaFecha = null;
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('resolucion_certamenes', 'fecha_termino_personalizada')) {
                    $this->fecha_termino_personalizada = $nuevaFecha;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('resolucion_certamenes', 'tiempo_adicional_minutos')) {
                    $this->tiempo_adicional_minutos = $minutosAdicionales;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('resolucion_certamenes', 'reabierto_por')) {
                    $this->reabierto_por = $userId ?? (auth()->check() ? auth()->id() : null);
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('resolucion_certamenes', 'motivo_reapertura')) {
                    $this->motivo_reapertura = $motivo;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('resolucion_certamenes', 'veces_reabierto')) {
                    $this->veces_reabierto = ($this->veces_reabierto ?? 0) + 1;
                }
            }

            $this->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getFechaTerminoEfectivaAttribute(): Carbon
    {
        if (array_key_exists('fecha_termino_personalizada', $this->attributes) && !empty($this->attributes['fecha_termino_personalizada'])) {
            return Carbon::parse($this->attributes['fecha_termino_personalizada']);
        }
        if ($this->certamen && !empty($this->certamen->fecha_termino)) {
            $base = Carbon::parse($this->certamen->fecha_termino);
            if (array_key_exists('tiempo_adicional_minutos', $this->attributes) && !empty($this->attributes['tiempo_adicional_minutos'])) {
                return $base->copy()->addMinutes((int)$this->attributes['tiempo_adicional_minutos']);
            }
            return $base;
        }
        return Carbon::now()->addHour();
    }

    public function reabiertoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reabierto_por');
    }

    public function usuario(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id_usuario');
    }

    public function certamen(): BelongsTo
    {
        return $this->BelongsTo(Certamenes::class, 'id_certamen');
    }

    public function envios(): HasMany
    {
        return $this->hasMany(EnvioSolucionProblema::class, 'id_certamen');
    }

    public function ProblemasSeleccionadas(): HasMany
    {
        return $this->hasMany(SeleccionProblemasCertamenes::class, 'id_res_certamen');
    }
}
