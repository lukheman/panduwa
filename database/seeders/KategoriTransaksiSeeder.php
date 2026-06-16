<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriTransaksi;

class KategoriTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        KategoriTransaksi::create([
            'nama_kategori' => 'Pembangunan Fisik',
            'deskripsi' => 'Pengeluaran untuk infrastruktur dan pembangunan desa',
        ]);
        
        KategoriTransaksi::create([
            'nama_kategori' => 'Pemberdayaan Masyarakat',
            'deskripsi' => 'Program pelatihan dan pemberdayaan',
        ]);
        
        KategoriTransaksi::create([
            'nama_kategori' => 'Operasional Desa',
            'deskripsi' => 'ATK, listrik, internet, dll',
        ]);
    }
}