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
                $_now = Carbon::now();
                if ($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino))) {
                    $this->fecha_finalizado = $_now;
                } else {
                    $this->fecha_finalizado = $certamen->fecha_termino;
                }
                $envios = $this->envios()->with('problema')->whereNotNull('termino')->orderBy('solucionado', 'asc')->get();
                //Tener registro de las penalizaciones por problema, asi no sobrepasar el limite establecido por el administrador o profesor (si es que ha impuesto penalización por error y limite de penalización)
                $problemas_penalizacion = [];
                foreach ($this->ProblemasSeleccionadas as $problema_seleccionado) {
                    $problemas_penalizacion[$problema_seleccionado->id_problema] = 0;
                }
                foreach ($envios as $envio) {
                    if ($envio->solucionado==true) {
                        $this->puntaje_obtenido += $envio->puntaje;
                        $this->problemas_resueltos += 1;
                    } else {
                        if ($problemas_penalizacion[$envio->problema->id] < $certamen->cantidad_penalizacion) {
                            $this->puntaje_obtenido -= $certamen->penalizacion_error;
                            $problemas_penalizacion[$envio->problema->id] += 1;
                        }
                    }
                }
                $this->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
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
