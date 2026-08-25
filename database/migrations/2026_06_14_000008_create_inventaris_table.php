<?php

use App\Enums\KondisiInventaris;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->date('tanggal_perolehan');
            $table->decimal('nilai_aset', 15, 2);
            $table->enum('kondisi', KondisiInventaris::values())->default(KondisiInventaris::BAIK->value);
            $table->foreignId('id_pengeluaran')->nullable()->constrained('pengeluaran')->onDelete('set null');
            $table->foreignId('id_bendahara')->constrained('bendahara')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
