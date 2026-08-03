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
        Schema::create('certamenes', function (Blueprint $table) {
            $table->id();
            $table->dateTime("fecha_inicio");
            $table->dateTime("fecha_termino");
            $table->string("nombre");
            $table->text("descripcion");
            $table->unsignedBigInteger("id_curso");
            $table->float("penalizacion_error")->default(0);
            $table->integer("cantidad_problemas")->default(0);
            $table->integer("puntaje_total")->default(0);
            $table->integer('cantidad_penalizacion')->default(0);
            $table->timestamps();

            $table->foreign('id_curso')->references('id')->on('cursos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certamenes');
    }
};
