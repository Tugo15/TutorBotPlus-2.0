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
        Schema::create('disponible', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_problema');
            $table->integer('cantidad_resueltos')->nullable()->default(0);
            $table->integer('cantidad_intentos')->nullable()->default(0);
            $table->bigInteger('tiempo_total')->nullable()->default(0);
            $table->integer('cant_retroalimentacion_solicitada')->nullable()->default(0);
            $table->foreign('id_curso')->references('id')->on('cursos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_problema')->references('id')->on('problemas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponible');
    }
};
