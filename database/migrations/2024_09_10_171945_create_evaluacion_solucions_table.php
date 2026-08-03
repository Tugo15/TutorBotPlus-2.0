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
        Schema::create('evaluacion_solucions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_envio');
            $table->string('token');
            $table->unsignedBigInteger('id_caso')->nullable();
            $table->enum('estado', ['Aceptado', 'Rechazado', 'Error', 'En Proceso'])->default('En Proceso');
            $table->string('resultado')->nullable();
            $table->text('error_compilacion')->nullable();
            $table->text('stout')->nullable();
            $table->string('tiempo')->nullable();
            $table->string('memoria')->nullable();
            $table->timestamps();

            $table->foreign('id_envio')->references('id')->on('envio_solucion_problemas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_caso')->references('id')->on('casos__pruebas')->onDelete('cascade')->onUpdate('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_solucions');
    }
};
