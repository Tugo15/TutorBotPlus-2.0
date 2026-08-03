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
        Schema::create('resolucion_certamenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_usuario");
            $table->unsignedBigInteger("id_certamen");
            $table->string("token");
            $table->integer("puntaje_obtenido")->default(0);
            $table->integer("problemas_resueltos")->default(0);
            $table->boolean("finalizado")->default(0);
            $table->dateTime("fecha_finalizado")->nullable();
            $table->timestamps();
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_certamen')->references('id')->on('certamenes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resolucion_certamenes');
    }
};
