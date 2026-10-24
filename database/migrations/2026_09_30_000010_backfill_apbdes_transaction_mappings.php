<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dds = DB::table('sumber_dana')->where('kode', 'DDS')->value('id');
        $pendapatan = DB::table('rekening_anggaran')->where('kode', '4.2.1')->value('id');

        if ($dds) {
            DB::table('pemasukan')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
            DB::table('pengeluaran')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
            DB::table('kegiatan')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
        }

        if ($pendapatan) {
            DB::table('pemasukan')->whereNull('rekening_anggaran_id')->update(['rekening_anggaran_id' => $pendapatan]);
        }
    }

    public function down(): void
    {
        // Mapping data lama tetap aman saat migrasi dibatalkan.
    }
};
