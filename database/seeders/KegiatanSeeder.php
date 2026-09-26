<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\SubBidangKegiatan;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    public function run(): void
    {
        $posyanduSub = SubBidangKegiatan::where('kode', '02.02')->first()
            ?? SubBidangKegiatan::first();

        $pelatihanSub = SubBidangKegiatan::where('kode', '04.02')->first()
            ?? SubBidangKegiatan::first();

        if (! $posyanduSub || ! $pelatihanSub) {
            return;
        }

        Kegiatan::firstOrCreate(
            ['nama_kegiatan' => 'Pembangunan Posyandu Mekar'],
            [
                'lokasi' => 'Dusun 1, RT 02/RW 01',
                'sub_bidang_kegiatan_id' => $posyanduSub->id,
                'rencana_anggaran' => 50000000.00,
                'status' => 'berjalan',
            ]
        );

        Kegiatan::firstOrCreate(
            ['nama_kegiatan' => 'Pelatihan Pertanian Organik'],
            [
                'lokasi' => 'Balai Desa',
                'sub_bidang_kegiatan_id' => $pelatihanSub->id,
                'rencana_anggaran' => 15000000.00,
                'status' => 'selesai',
            ]
        );
    }
}
