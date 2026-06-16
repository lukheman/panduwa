<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengeluaran;

class PengeluaranSeeder extends Seeder
{
    public function run(): void
    {
        Pengeluaran::create([
            'id_kategori_transaksi' => 1, // Pembangunan Fisik
            'jumlah' => 20000000.00,
            'tanggal' => '2023-03-05',
            'keterangan' => 'Pembelian semen dan material posyandu',
            'id_kegiatan' => 1, // Posyandu Mekar
        ]);
        
        Pengeluaran::create([
            'id_kategori_transaksi' => 3, // Operasional Desa
            'jumlah' => 5000000.00,
            'tanggal' => '2023-03-10',
            'keterangan' => 'Pembelian Laptop Kantor',
            'id_kegiatan' => null, // Tidak terikat kegiatan
        ]);
    }
}