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
        Schema::table('resolucion_certamenes', function (Blueprint $table) {
            if (!Schema::hasColumn('resolucion_certamenes', 'fecha_termino_personalizada')) {
                $table->dateTime('fecha_termino_personalizada')->nullable()->after('fecha_finalizado');
            }
            if (!Schema::hasColumn('resolucion_certamenes', 'tiempo_adicional_minutos')) {
                $table->integer('tiempo_adicional_minutos')->default(0)->after('fecha_termino_personalizada');
            }
            if (!Schema::hasColumn('resolucion_certamenes', 'reabierto_por')) {
                $table->unsignedBigInteger('reabierto_por')->nullable()->after('tiempo_adicional_minutos');
            }
            if (!Schema::hasColumn('resolucion_certamenes', 'motivo_reapertura')) {
                $table->text('motivo_reapertura')->nullable()->after('reabierto_por');
            }
            if (!Schema::hasColumn('resolucion_certamenes', 'veces_reabierto')) {
                $table->integer('veces_reabierto')->default(0)->after('motivo_reapertura');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resolucion_certamenes', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['veces_reabierto', 'motivo_reapertura', 'reabierto_por', 'tiempo_adicional_minutos', 'fecha_termino_personalizada'] as $col) {
                if (Schema::hasColumn('resolucion_certamenes', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

