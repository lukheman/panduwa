<?php

namespace Database\Seeders;

use App\Models\BidangKegiatan;
use Illuminate\Database\Seeder;

class BidangSubBidangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode' => '01',
                'nama' => 'Penyelenggaraan Pemerintahan Desa',
                'deskripsi' => 'Belanja penghasilan tetap, operasional, sarana prasarana, administrasi, dan pertanahan.',
                'subs' => [
                    ['kode' => '01.01', 'nama' => 'Penyelenggaraan Belanja Penghasilan Tetap, Tunjangan dan Operasional Pemerintahan Desa'],
                    ['kode' => '01.02', 'nama' => 'Sarana dan Prasarana Pemerintahan Desa'],
                    ['kode' => '01.03', 'nama' => 'Administrasi Kependudukan, Pencatatan Sipil, Statistik dan Kearsipan'],
                    ['kode' => '01.04', 'nama' => 'Tata Praja Pemerintahan, Perencanaan, Keuangan dan Pelaporan'],
                    ['kode' => '01.05', 'nama' => 'Pertanahan'],
                ],
            ],
            [
                'kode' => '02',
                'nama' => 'Pelaksanaan Pembangunan Desa',
                'deskripsi' => 'Pembangunan pendidikan, kesehatan, pekerjaan umum, permukiman, dan lingkungan.',
                'subs' => [
                    ['kode' => '02.01', 'nama' => 'Pendidikan'],
                    ['kode' => '02.02', 'nama' => 'Kesehatan'],
                    ['kode' => '02.03', 'nama' => 'Pekerjaan Umum dan Penataan Ruang'],
                    ['kode' => '02.04', 'nama' => 'Kawasan Permukiman'],
                    ['kode' => '02.05', 'nama' => 'Kehutanan dan Lingkungan Hidup'],
                    ['kode' => '02.06', 'nama' => 'Perhubungan, Komunikasi dan Informatika'],
                    ['kode' => '02.07', 'nama' => 'Energi dan Sumber Daya Mineral'],
                    ['kode' => '02.08', 'nama' => 'Pariwisata'],
                ],
            ],
            [
                'kode' => '03',
                'nama' => 'Pembinaan Kemasyarakatan Desa',
                'deskripsi' => 'Ketenteraman, kebudayaan, kepemudaan, dan kelembagaan masyarakat.',
                'subs' => [
                    ['kode' => '03.01', 'nama' => 'Ketenteraman, Ketertiban Umum dan Perlindungan Masyarakat'],
                    ['kode' => '03.02', 'nama' => 'Kebudayaan dan Keagamaan'],
                    ['kode' => '03.03', 'nama' => 'Kepemudaan dan Olahraga'],
                    ['kode' => '03.04', 'nama' => 'Kelembagaan Masyarakat'],
                ],
            ],
            [
                'kode' => '04',
                'nama' => 'Pemberdayaan Masyarakat Desa',
                'deskripsi' => 'Kelautan, pertanian, peningkatan kapasitas, perempuan, UMKM, dan perdagangan.',
                'subs' => [
                    ['kode' => '04.01', 'nama' => 'Kelautan dan Perikanan'],
                    ['kode' => '04.02', 'nama' => 'Pertanian dan Peternakan'],
                    ['kode' => '04.03', 'nama' => 'Peningkatan Kapasitas Aparatur Desa'],
                    ['kode' => '04.04', 'nama' => 'Pemberdayaan Perempuan, Perlindungan Anak dan Keluarga'],
                    ['kode' => '04.05', 'nama' => 'Koperasi, Usaha Mikro Kecil dan Menengah (UMKM)'],
                    ['kode' => '04.06', 'nama' => 'Dukungan Penanaman Modal'],
                    ['kode' => '04.07', 'nama' => 'Perdagangan dan Perindustrian'],
                ],
            ],
            [
                'kode' => '05',
                'nama' => 'Penanggulangan Bencana, Keadaan Darurat dan Mendesak Desa',
                'deskripsi' => 'Penanggulangan bencana, keadaan darurat, dan keadaan mendesak.',
                'subs' => [
                    ['kode' => '05.01', 'nama' => 'Penanggulangan Bencana'],
                    ['kode' => '05.02', 'nama' => 'Keadaan Darurat'],
                    ['kode' => '05.03', 'nama' => 'Keadaan Mendesak'],
                ],
            ],
        ];

        foreach ($data as $bidangData) {
            $bidang = BidangKegiatan::firstOrCreate(
                ['kode' => $bidangData['kode']],
                ['nama' => $bidangData['nama'], 'deskripsi' => $bidangData['deskripsi']]
            );

            foreach ($bidangData['subs'] as $sub) {
                $bidang->subBidangs()->firstOrCreate(
                    ['kode' => $sub['kode']],
                    ['nama' => $sub['nama']]
                );
            }
        }
    }
}
