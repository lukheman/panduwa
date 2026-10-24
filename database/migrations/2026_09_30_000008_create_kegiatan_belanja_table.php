<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_belanja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('rekening_anggaran_id')->nullable()->constrained('rekening_anggaran')->nullOnDelete();
            $table->string('kode_rekening', 30)->nullable();
            $table->string('uraian');
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->foreignId('kegiatan_belanja_id')->nullable()->after('id_kegiatan')->constrained('kegiatan_belanja')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kegiatan_belanja_id');
        });
        Schema::dropIfExists('kegiatan_belanja');
    }
};
