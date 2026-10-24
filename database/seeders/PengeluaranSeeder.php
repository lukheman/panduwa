<?php

namespace Database\Seeders;

use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;

class PengeluaranSeeder extends Seeder
{
    public function run(): void
    {
        $dds = SumberDana::where('kode', 'DDS')->value('id');
        $rekening = RekeningAnggaran::where('kode', '5.2')->value('id');

        Pengeluaran::create([

            'jumlah' => 20000000.00,
            'sumber_dana_id' => $dds,
            'rekening_anggaran_id' => $rekening,
            'tanggal' => '2023-03-05',
            'keterangan' => 'Pembelian semen dan material posyandu',
            'id_kegiatan' => 1, // Posyandu Mekar
        ]);

        Pengeluaran::create([

            'jumlah' => 5000000.00,
            'sumber_dana_id' => $dds,
            'rekening_anggaran_id' => $rekening,
            'tanggal' => '2023-03-10',
            'keterangan' => 'Pembelian Laptop Kantor',
            'id_kegiatan' => null, // Tidak terikat kegiatan
        ]);
    }
}
