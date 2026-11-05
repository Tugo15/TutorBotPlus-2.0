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
        // 1. Detectar nombres de categorías duplicadas
        $duplicadas = DB::table('categoria__problemas')
            ->select('nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('nombre')
            ->having('total', '>', 1)
            ->pluck('nombre');

        foreach ($duplicadas as $nombre) {
            $registros = DB::table('categoria__problemas')
                ->where('nombre', $nombre)
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();

            // Conservar el primer ID creado como ID oficial
            $id_principal = array_shift($registros);

            foreach ($registros as $id_duplicado) {
                // Actualizar referencias en tabla 'pertenece'
                $problemasConDuplicado = DB::table('pertenece')
                    ->where('id_categoria', $id_duplicado)
                    ->pluck('id_problema');

                foreach ($problemasConDuplicado as $id_problema) {
                    $yaTienePrincipal = DB::table('pertenece')
                        ->where('id_problema', $id_problema)
                        ->where('id_categoria', $id_principal)
                        ->exists();

                    if (!$yaTienePrincipal) {
                        DB::table('pertenece')
                            ->where('id_problema', $id_problema)
                            ->where('id_categoria', $id_duplicado)
                            ->update(['id_categoria' => $id_principal]);
                    } else {
                        DB::table('pertenece')
                            ->where('id_problema', $id_problema)
                            ->where('id_categoria', $id_duplicado)
                            ->delete();
                    }
                }

                // Actualizar referencias en tabla 'banco_problemas_certamenes'
                $certamenesConDuplicado = DB::table('banco_problemas_certamenes')
                    ->where('id_categoria', $id_duplicado)
                    ->pluck('id_certamen');

                foreach ($certamenesConDuplicado as $id_certamen) {
                    $yaTienePrincipal = DB::table('banco_problemas_certamenes')
                        ->where('id_certamen', $id_certamen)
                        ->where('id_categoria', $id_principal)
                        ->exists();

                    if (!$yaTienePrincipal) {
                        DB::table('banco_problemas_certamenes')
                            ->where('id_certamen', $id_certamen)
                            ->where('id_categoria', $id_duplicado)
                            ->update(['id_categoria' => $id_principal]);
                    } else {
                        DB::table('banco_problemas_certamenes')
                            ->where('id_certamen', $id_certamen)
                            ->where('id_categoria', $id_duplicado)
                            ->delete();
                    }
                }

                // Eliminar el registro duplicado de categoria__problemas
                DB::table('categoria__problemas')->where('id', $id_duplicado)->delete();
            }
        }

        // 2. Agregar restricción unique para que nunca más vuelva a duplicarse
        Schema::table('categoria__problemas', function (Blueprint $table) {
            $table->unique('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria__problemas', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
    }
};

