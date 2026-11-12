<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProblemasController;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Migrar imágenes en base64 en problemas existentes
            $problemas = DB::table('problemas')
                ->where('body_problema', 'LIKE', '%data:image/%')
                ->get();

            foreach ($problemas as $p) {
                $actualizado = ProblemasController::almacenarImagenesBase64($p->body_problema);
                if ($actualizado !== $p->body_problema) {
                    DB::table('problemas')
                        ->where('id', $p->id)
                        ->update(['body_problema' => $actualizado]);
                }
            }

            // Migrar imágenes en base64 en certámenes existentes
            $certamenes = DB::table('certamenes')
                ->where('descripcion', 'LIKE', '%data:image/%')
                ->get();

            foreach ($certamenes as $c) {
                $actualizado = ProblemasController::almacenarImagenesBase64($c->descripcion);
                if ($actualizado !== $c->descripcion) {
                    DB::table('certamenes')
                        ->where('id', $c->id)
                        ->update(['descripcion' => $actualizado]);
                }
            }
        } catch (\Throwable $e) {
            // No bloquear la migración si la base de datos está vacía o en tests
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Operación irreversible (no se vuelve a incrustar base64)
    }
};

