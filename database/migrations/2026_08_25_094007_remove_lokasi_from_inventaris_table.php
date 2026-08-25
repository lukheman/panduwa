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
        if (Schema::hasColumn('inventaris', 'lokasi')) {
            Schema::table('inventaris', function (Blueprint $table) {
                $table->dropColumn('lokasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('inventaris', 'lokasi')) {
            Schema::table('inventaris', function (Blueprint $table) {
                $table->string('lokasi')->after('nama_barang');
            });
        }
    }
};
