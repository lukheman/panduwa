<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->unsignedInteger('jumlah')->default(1)->after('nama_barang');
            $table->foreignId('sumber_dana_id')->nullable()->after('kondisi')->constrained('sumber_dana')->nullOnDelete();
            $table->text('keterangan')->nullable()->after('sumber_dana_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_id');
            $table->dropColumn(['jumlah', 'keterangan']);
        });
    }
};
