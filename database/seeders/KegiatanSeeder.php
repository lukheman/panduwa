<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    public function run(): void
    {
        Kegiatan::create([
            'nama_kegiatan' => 'Pembangunan Posyandu Mekar',
            'lokasi' => 'Dusun 1, RT 02/RW 01',
            'kelompok' => 'bidang_pembangunan_desa',
            'rencana_anggaran' => 50000000.00,
            'status' => 'berjalan',
        ]);

        Kegiatan::create([
            'nama_kegiatan' => 'Pelatihan Pertanian Organik',
            'lokasi' => 'Balai Desa',
            'kelompok' => 'bidang_pemberdayaan_masyarakat',
            'rencana_anggaran' => 15000000.00,
            'status' => 'selesai',
        ]);
    }
}
