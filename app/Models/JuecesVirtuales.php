<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EnvioSolucionProblema;
class JuecesVirtuales extends Model
{
    use HasFactory;

    public static function generateHeaderRequest(JuecesVirtuales $juez){
        if(!isset($juez->autenticacion)){
            return [];
        }
        if($juez->autenticacion == "x-rapid-key"){
            return [
                'x-rapidapi-host' => $juez->host,
                'x-rapidapi-key' => $juez->api_token
                ];
        }else{
            return [
                $juez->autenticacion => $juez->api_token,
            ];
        }
    }
    public function envios(): HasMany
    {
        return $this->hasMany(EnvioSolucionProblema::class, 'id_juez');
    }
}
