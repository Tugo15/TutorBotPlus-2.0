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
        Schema::table('certamenes', function (Blueprint $table) {
            if (!Schema::hasColumn('certamenes', 'problemas_seleccionados')) {
                $table->text('problemas_seleccionados')->nullable()->after('cantidad_problemas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certamenes', function (Blueprint $table) {
            if (Schema::hasColumn('certamenes', 'problemas_seleccionados')) {
                $table->dropColumn('problemas_seleccionados');
            }
        });
    }
};

