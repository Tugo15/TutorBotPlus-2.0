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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE problemas MODIFY body_problema LONGTEXT NOT NULL');
            DB::statement('ALTER TABLE problemas MODIFY body_problema_resumido MEDIUMTEXT NULL');
            if (Schema::hasColumn('problemas', 'body_editorial')) {
                DB::statement('ALTER TABLE problemas MODIFY body_editorial LONGTEXT NULL');
            }
            if (Schema::hasTable('certamens') && Schema::hasColumn('certamens', 'descripcion')) {
                DB::statement('ALTER TABLE certamens MODIFY descripcion LONGTEXT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE problemas MODIFY body_problema TEXT NOT NULL');
            DB::statement('ALTER TABLE problemas MODIFY body_problema_resumido TEXT NULL');
            if (Schema::hasColumn('problemas', 'body_editorial')) {
                DB::statement('ALTER TABLE problemas MODIFY body_editorial TEXT NULL');
            }
            if (Schema::hasTable('certamens') && Schema::hasColumn('certamens', 'descripcion')) {
                DB::statement('ALTER TABLE certamens MODIFY descripcion TEXT NULL');
            }
        }
    }
};
