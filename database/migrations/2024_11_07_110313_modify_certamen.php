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
        if (!Schema::hasColumn('certamenes', 'cantidad_penalizacion')) {
            Schema::table('certamenes', function (Blueprint $table) {
                $table->integer('cantidad_penalizacion')->default(0);
            });      
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
