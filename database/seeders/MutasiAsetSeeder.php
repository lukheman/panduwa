<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MutasiAset;

class MutasiAsetSeeder extends Seeder
{
    public function run(): void
    {
        MutasiAset::create([
            'id_inventaris' => 1, // Laptop
            'jenis_mutasi' => 'Peminjaman',
            'tanggal' => '2023-04-01',
            'keterangan' => 'Dipinjam oleh staf untuk dinas luar',
            'id_bendahara' => 1,
        ]);
        
        MutasiAset::create([
            'id_inventaris' => 2, // Printer
            'jenis_mutasi' => 'Perawatan',
            'tanggal' => '2023-05-20',
            'keterangan' => 'Isi ulang tinta dan pembersihan head',
            'id_bendahara' => 1,
        ]);
    }
}