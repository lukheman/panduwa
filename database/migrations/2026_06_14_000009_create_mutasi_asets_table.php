<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_inventaris')->constrained('inventaris')->onDelete('cascade');
            $table->string('jenis_mutasi');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('id_bendahara')->constrained('bendahara')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_aset');
    }
};