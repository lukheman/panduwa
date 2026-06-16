<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kegiatan;

class KegiatanSeeder extends Seeder
{
    public function run(): void
    {
        Kegiatan::create([
            'nama_kegiatan' => 'Pembangunan Posyandu Mekar',
            'lokasi' => 'Dusun 1, RT 02/RW 01',
            'anggaran' => 50000000.00,
            'status' => 'berjalan',
            'id_bendahara' => 1,
        ]);

        Kegiatan::create([
            'nama_kegiatan' => 'Pelatihan Pertanian Organik',
            'lokasi' => 'Balai Desa',
            'anggaran' => 15000000.00,
            'status' => 'selesai',
            'id_bendahara' => 1,
        ]);
    }
}
