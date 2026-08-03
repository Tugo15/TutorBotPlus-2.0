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
        Schema::create('casos__pruebas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('id_problema')->nullable();
            $table->text('entradas')->nullable();
            $table->text('salidas')->nullable();
            $table->boolean('ejemplo')->default(false);
            $table->integer('puntos')->default(0);

            $table->foreign('id_problema')->references('id')->on('problemas')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casos__pruebas');
    }
};
