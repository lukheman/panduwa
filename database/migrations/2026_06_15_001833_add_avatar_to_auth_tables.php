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
        Schema::table('admin', function (Blueprint $table) {
            $table->string('avatar')->nullable();
        });
        Schema::table('bendahara', function (Blueprint $table) {
            $table->string('avatar')->nullable();
        });
        Schema::table('kepala_desa', function (Blueprint $table) {
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::table('bendahara', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::table('kepala_desa', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
