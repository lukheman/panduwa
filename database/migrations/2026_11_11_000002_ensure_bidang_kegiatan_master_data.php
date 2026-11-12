<?php

use Database\Seeders\BidangSubBidangSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Pastikan master bidang dan sub-bidang APBDes tersedia.
     *
     * Mengacu pada Permendagri Nomor 20 Tahun 2018 Pasal 16-17:
     * 5 bidang dan 27 sub-bidang klasifikasi belanja Desa.
     */
    public function up(): void
    {
        (new BidangSubBidangSeeder)->run();
    }

    public function down(): void
    {
        // Master data tetap dipertahankan saat migrasi dibatalkan.
    }
};
