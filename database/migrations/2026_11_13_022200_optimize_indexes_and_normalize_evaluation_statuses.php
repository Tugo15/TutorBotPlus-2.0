<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalizar estados históricos en inglés a español en evaluacion_solucions
        $mapResultados = [
            'Accepted' => 'Aceptado',
            'Wrong Answer' => 'Respuesta Incorrecta',
            'Time Limit Exceeded' => 'Tiempo Límite Excedido',
            'Compilation Error' => 'Error de Compilación',
            'Internal Error' => 'Error Interno',
        ];
        foreach ($mapResultados as $en => $es) {
            DB::table('evaluacion_solucions')->where('resultado', $en)->update(['resultado' => $es]);
        }

        // 2. Índices compuestos para acelerar consultas de informes y certámenes
        Schema::table('envio_solucion_problemas', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM envio_solucion_problemas"))->pluck('Key_name')->toArray();
            if (!in_array('idx_envio_certamen_termino_sol', $indexes)) {
                $table->index(['id_certamen', 'termino', 'solucionado'], 'idx_envio_certamen_termino_sol');
            }
            if (!in_array('idx_envio_cursa_resolver_termino', $indexes)) {
                $table->index(['id_cursa', 'id_resolver', 'termino'], 'idx_envio_cursa_resolver_termino');
            }
        });

        Schema::table('evaluacion_solucions', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM evaluacion_solucions"))->pluck('Key_name')->toArray();
            if (!in_array('idx_eval_sol_envio_estado', $indexes)) {
                $table->index(['id_envio', 'estado'], 'idx_eval_sol_envio_estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envio_solucion_problemas', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM envio_solucion_problemas"))->pluck('Key_name')->toArray();
            if (in_array('idx_envio_certamen_termino_sol', $indexes)) {
                $table->dropIndex('idx_envio_certamen_termino_sol');
            }
            if (in_array('idx_envio_cursa_resolver_termino', $indexes)) {
                $table->dropIndex('idx_envio_cursa_resolver_termino');
            }
        });

        Schema::table('evaluacion_solucions', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM evaluacion_solucions"))->pluck('Key_name')->toArray();
            if (in_array('idx_eval_sol_envio_estado', $indexes)) {
                $table->dropIndex('idx_eval_sol_envio_estado');
            }
        });
    }
};

