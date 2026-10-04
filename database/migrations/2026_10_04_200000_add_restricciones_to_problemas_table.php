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
        Schema::table('problemas', function (Blueprint $table) {
            $table->text('restricciones')->nullable()->after('body_problema_resumido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problemas', function (Blueprint $table) {
            $table->dropColumn('restricciones');
        });
    }
};
