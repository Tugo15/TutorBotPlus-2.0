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
        if (!Schema::hasColumn('disponible', 'visible')) {
            Schema::table('disponible', function (Blueprint $table) {
                $table->boolean('visible')->default(true)->after('id_problema');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('disponible', 'visible')) {
            Schema::table('disponible', function (Blueprint $table) {
                $table->dropColumn('visible');
            });
        }
    }
};

