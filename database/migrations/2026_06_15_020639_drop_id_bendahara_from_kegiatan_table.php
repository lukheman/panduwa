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
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropForeign(['id_bendahara']);
            $table->dropColumn('id_bendahara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_bendahara')->nullable();
            $table->foreign('id_bendahara')->references('id')->on('bendahara')->onDelete('cascade');
        });
    }
};
