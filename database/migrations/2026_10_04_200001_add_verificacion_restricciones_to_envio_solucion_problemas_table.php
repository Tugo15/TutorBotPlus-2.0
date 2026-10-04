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
        Schema::table('envio_solucion_problemas', function (Blueprint $table) {
            $table->text('verificacion_restricciones')->nullable()->after('codigo');
            $table->boolean('cumple_restricciones')->nullable()->after('verificacion_restricciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envio_solucion_problemas', function (Blueprint $table) {
            $table->dropColumn(['verificacion_restricciones', 'cumple_restricciones']);
        });
    }
};
