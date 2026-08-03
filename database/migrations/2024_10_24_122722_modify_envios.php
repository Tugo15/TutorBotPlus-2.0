<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EnvioSolucionProblema;
use App\Models\Resolver;
use App\Models\Cursa;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('envio_solucion_problemas', 'id_curso')) {
            if(!Schema::hasColumn('envio_solucion_problemas', 'id_cursa')){   
                Schema::table('envio_solucion_problemas', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_cursa')->nullable();
                    $table->unsignedBigInteger('id_resolver')->nullable();

                    $table->foreign('id_cursa')->references('id')->on('cursa')->onDelete('cascade')->onUpdate('cascade');
                    $table->foreign('id_resolver')->references('id')->on('resolver')->onDelete('set null')->onUpdate('cascade');
                });
            }
            $envios = EnvioSolucionProblema::all();
            foreach($envios as $envio){
                $id_curso = $envio->id_curso;
                $id_usuario = $envio->id_usuario;
                $id_problema = $envio->id_problema;
                $id_lenguaje = $envio->id_lenguaje;
                $id_cursa = Cursa::where('id_usuario', '=', $id_usuario)->where('id_curso', '=', $id_curso)->first()->id;
                if(isset($id_lenguaje)){
                    $id_resolver = Resolver::where('id_lenguaje', '=', $id_lenguaje)->where('id_problema', '=', $id_problema)->first()->id;
                }else{
                    $id_resolver = Resolver::where('id_problema','=',$id_problema)->inRandomOrder()->first()->id;
                }
                $envio->id_cursa = $id_cursa;
                $envio->id_resolver = $id_resolver;

                $envio->save();
            }

            Schema::table('envio_solucion_problemas', function (Blueprint $table) {
                $table->dropForeign(['id_usuario']);  
                $table->dropForeign(['id_curso']);  
                $table->dropForeign(['id_problema']);  
                $table->dropForeign(['id_lenguaje']);  
                $table->dropColumn(['id_curso', 'id_usuario', 'id_lenguaje', 'id_problema']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
