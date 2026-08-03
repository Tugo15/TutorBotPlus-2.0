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
        Schema::create('banco_problemas_certamenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_categoria");
            $table->unsignedBigInteger("id_certamen");
            $table->timestamps();
            $table->foreign('id_categoria')->references('id')->on('categoria__problemas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_certamen')->references('id')->on('certamenes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banco_problemas_certamenes');
    }
};
