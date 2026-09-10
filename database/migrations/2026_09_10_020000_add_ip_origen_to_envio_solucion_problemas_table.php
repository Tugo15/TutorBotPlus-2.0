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
            $table->string('ip_origen', 45)->nullable()->after('solucionado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envio_solucion_problemas', function (Blueprint $table) {
            $table->dropColumn('ip_origen');
        });
    }
};
