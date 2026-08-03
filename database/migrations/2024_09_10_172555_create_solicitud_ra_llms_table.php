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
        Schema::create('solicitud_ra_llms', function (Blueprint $table) {
            $table->id();
            $table->text('retroalimentacion');
            $table->unsignedBigInteger('id_envio');
            $table->unsignedBigInteger('id_prompt')->nullable();
            $table->unsignedBigInteger('id_llm')->nullable();
            $table->timestamps();

            $table->foreign('id_envio')->references('id')->on('envio_solucion_problemas')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_ra_llms');
    }
};
