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
        Schema::table('problemas', function (Blueprint $table) {
            $table->enum('dificultad', ['Fácil', 'Medio', 'Difícil'])->default('Medio')->after('nombre');
        });

        Schema::table('certamenes', function (Blueprint $table) {
            $table->enum('dificultad', ['Fácil', 'Medio', 'Difícil'])->default('Medio')->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problemas', function (Blueprint $table) {
            $table->dropColumn('dificultad');
        });

        Schema::table('certamenes', function (Blueprint $table) {
            $table->dropColumn('dificultad');
        });
    }
};
