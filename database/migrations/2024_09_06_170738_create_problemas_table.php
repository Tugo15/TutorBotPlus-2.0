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
        Schema::create('problemas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('codigo')->nullable();
            $table->text('body_problema');
            $table->text('body_problema_resumido')->nullable();
            $table->string('memoria_limite')->default('5000');
            $table->string('archivo_adicional')->nullable();
            $table->string('tiempo_limite')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_termino')->nullable();
            $table->boolean('visible')->default(true);
            $table->boolean('habilitar_llm')->default(false);
            $table->integer('limite_llm')->default(0);
            $table->integer('puntaje_total')->nullable()->default(0);
            $table->text('body_editorial')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problemas');
    }
};
