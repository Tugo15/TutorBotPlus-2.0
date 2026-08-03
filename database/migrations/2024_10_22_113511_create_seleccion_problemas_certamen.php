<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasTable('seleccion_problemas_certamen')){
            Schema::create('seleccion_problemas_certamen', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_res_certamen');
                $table->unsignedBigInteger('id_problema');
                $table->timestamps();
    
                $table->foreign('id_res_certamen')->references('id')->on('resolucion_certamenes')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('id_problema')->references('id')->on('problemas')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seleccion_problemas_certamen');
    }
};
