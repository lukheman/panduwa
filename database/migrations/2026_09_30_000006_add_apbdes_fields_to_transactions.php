<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->foreignId('sumber_dana_id')->nullable()->after('id')->constrained('sumber_dana')->nullOnDelete();
            $table->foreignId('rekening_anggaran_id')->nullable()->after('sumber_dana_id')->constrained('rekening_anggaran')->nullOnDelete();
        });
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->foreignId('sumber_dana_id')->nullable()->after('id')->constrained('sumber_dana')->nullOnDelete();
            $table->foreignId('rekening_anggaran_id')->nullable()->after('sumber_dana_id')->constrained('rekening_anggaran')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rekening_anggaran_id');
            $table->dropConstrainedForeignId('sumber_dana_id');
        });
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rekening_anggaran_id');
            $table->dropConstrainedForeignId('sumber_dana_id');
        });
    }
};
