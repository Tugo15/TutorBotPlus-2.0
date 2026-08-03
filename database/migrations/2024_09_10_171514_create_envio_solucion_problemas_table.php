<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('envio_solucion_problemas', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->unsignedBigInteger('id_cursa')->nullable();
            $table->unsignedBigInteger('id_resolver')->nullable();
            $table->unsignedBigInteger('id_certamen')->nullable();
            $table->unsignedBigInteger('id_juez')->nullable();
            $table->text('codigo')->nullable();
            $table->dateTime('inicio')->default(Carbon::now());
            $table->dateTime('termino')->nullable();
            $table->integer('cant_casos_resuelto')->default(0);
            $table->integer('puntaje')->default(0);
            $table->boolean('solucionado')->default(false);
            $table->timestamps();

            $table->foreign('id_cursa')->references('id')->on('cursa')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_resolver')->references('id')->on('resolver')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('id_juez')->references('id')->on('jueces_virtuales')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_certamen')->references('id')->on('resolucion_certamenes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_solucion_problemas');
    }
};
