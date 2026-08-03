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
        Schema::create('resolver', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('id_problema');
            $table->unsignedBigInteger('id_lenguaje')->nullable();

            $table->foreign('id_problema')->references('id')->on('problemas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_lenguaje')->references('id')->on('lenguajes_programaciones')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resolver');
    }
};
