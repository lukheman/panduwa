<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->string('kode_rekening', 30)->nullable()->after('nama_kegiatan');
            $table->foreignId('sumber_dana_id')->nullable()->after('kode_rekening')->constrained('sumber_dana')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_id');
            $table->dropColumn('kode_rekening');
        });
    }
};
