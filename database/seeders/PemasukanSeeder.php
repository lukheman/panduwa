<?php

namespace Database\Seeders;

use App\Models\Pemasukan;
use App\Models\RekeningAnggaran;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;

class PemasukanSeeder extends Seeder
{
    public function run(): void
    {
        $dds = SumberDana::where('kode', 'DDS')->value('id');
        $add = SumberDana::where('kode', 'ADD')->value('id');
        $rekeningDds = RekeningAnggaran::where('kode', '4.2.1')->value('id');
        $rekeningAdd = RekeningAnggaran::where('kode', '4.2.2')->value('id');

        Pemasukan::create([
            'sumber_dana' => 'Dana Desa (APBN)',
            'sumber_dana_id' => $dds,
            'rekening_anggaran_id' => $rekeningDds,
            'jumlah' => 150000000.00,
            'anggaran' => 150000000.00,
            'tanggal' => '2023-01-15',
            'keterangan' => 'Pencairan tahap 1',
        ]);

        Pemasukan::create([
            'sumber_dana' => 'Alokasi Dana Desa (APBD)',
            'sumber_dana_id' => $add,
            'rekening_anggaran_id' => $rekeningAdd,
            'jumlah' => 80000000.00,
            'anggaran' => 80000000.00,
            'tanggal' => '2023-02-10',
            'keterangan' => 'Pencairan tahap 1 ADD',
        ]);
    }
}
