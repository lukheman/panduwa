<?php

use App\Enums\KelompokKegiatan;
use App\Enums\StatusKegiatan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->string('lokasi');
            $table->enum('kelompok', KelompokKegiatan::values());
            $table->decimal('rencana_anggaran', 15, 2);
            $table->decimal('realisasi_anggaran', 15, 2)->nullable();
            $table->enum('status', StatusKegiatan::values())->default(StatusKegiatan::PERENCANAAN->value);
            $table->string('foto_progres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
