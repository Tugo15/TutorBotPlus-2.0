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
            $table->boolean('restriccion_red')->default(false)->after('cantidad_penalizacion');
            $table->text('ips_autorizadas')->nullable()->after('restriccion_red');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certamenes', function (Blueprint $table) {
            $table->dropColumn(['restriccion_red', 'ips_autorizadas']);
        });
    }
};
