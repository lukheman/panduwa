<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_bidang_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_kegiatan_id')->constrained('bidang_kegiatan')->onDelete('cascade');
            $table->string('kode', 20);
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['bidang_kegiatan_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_bidang_kegiatan');
    }
};
