<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pemasukan;

class PemasukanSeeder extends Seeder
{
    public function run(): void
    {
        Pemasukan::create([
            'sumber_dana' => 'Dana Desa (APBN)',
            'jumlah' => 150000000.00,
            'tanggal' => '2023-01-15',
            'keterangan' => 'Pencairan tahap 1',
            'id_bendahara' => 1,
        ]);

        Pemasukan::create([
            'sumber_dana' => 'Alokasi Dana Desa (APBD)',
            'jumlah' => 80000000.00,
            'tanggal' => '2023-02-10',
            'keterangan' => 'Pencairan tahap 1 ADD',
            'id_bendahara' => 1,
        ]);
    }
}
