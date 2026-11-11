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
            if (!Schema::hasColumn('certamenes', 'bancos_preguntas')) {
                $table->text('bancos_preguntas')->nullable()->after('problemas_seleccionados');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certamenes', function (Blueprint $table) {
            if (Schema::hasColumn('certamenes', 'bancos_preguntas')) {
                $table->dropColumn('bancos_preguntas');
            }
        });
    }
};

