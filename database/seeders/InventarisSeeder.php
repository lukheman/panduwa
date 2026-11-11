<?php

namespace Database\Seeders;

use App\Enums\KondisiInventaris;
use App\Models\Inventaris;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;

class InventarisSeeder extends Seeder
{
    public function run(): void
    {
        $danaDesa = SumberDana::where('kode', SumberDana::KODE_DANA_DESA)->value('id');

        Inventaris::create([
            'kode_barang' => 'INV/2023/001',
            'nama_barang' => 'Laptop Asus VivoBook',
            'jumlah' => 1,
            'tanggal_perolehan' => '2023-03-10',
            'nilai_aset' => 5000000.00,
            'kondisi' => KondisiInventaris::BAIK->value,
            'sumber_dana_id' => $danaDesa,
            'id_pengeluaran' => 2, // Merujuk ke pengeluaran laptop
        ]);

        Inventaris::create([
            'kode_barang' => 'INV/2023/002',
            'nama_barang' => 'Printer Epson L3110',
            'jumlah' => 1,
            'tanggal_perolehan' => '2022-05-15',
            'nilai_aset' => 2500000.00,
            'kondisi' => KondisiInventaris::BAIK->value,
            'sumber_dana_id' => $danaDesa,
            'id_pengeluaran' => null, // Mungkin dibeli tahun lalu
        ]);
    }
}
