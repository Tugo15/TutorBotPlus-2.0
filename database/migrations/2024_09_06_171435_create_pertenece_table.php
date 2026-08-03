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
        Schema::create('pertenece', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('id_problema');
            $table->unsignedBigInteger('id_categoria');

            $table->foreign('id_problema')->references('id')->on('problemas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_categoria')->references('id')->on('categoria__problemas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertenece');
    }
};
