<?php

namespace Database\Seeders;

use App\Enums\KondisiInventaris;
use App\Enums\StatusKegiatan;
use App\Models\Inventaris;
use App\Models\Kegiatan;
use App\Models\KegiatanBelanja;
use App\Models\Pemasukan;
use App\Models\Pembiayaan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SubBidangKegiatan;
use App\Models\SumberDana;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PenggunaanDanaSeeder extends Seeder
{
    private int $tahun;

    public function run(): void
    {
        $this->tahun = (int) now()->year;

        $this->call(BidangSubBidangSeeder::class);
        $this->siapkanRekening();

        $dds = SumberDana::firstOrCreate(
            ['kode' => SumberDana::KODE_DANA_DESA],
            ['nama' => 'Dana Desa', 'keterangan' => 'Dana Desa, dropping APBN', 'aktif' => true]
        );

        $add = SumberDana::firstOrCreate(
            ['kode' => SumberDana::KODE_ALOKASI_DANA_DESA],
            ['nama' => 'Alokasi Dana Desa', 'keterangan' => 'Alokasi Dana Desa, APBD', 'aktif' => true]
        );

        $this->seedPemasukan($dds, $add);
        $this->seedKegiatanDds($dds);
        $this->seedKegiatanAdd($add);
        $this->seedPembiayaan($dds, $add);
        $this->seedInventaris($dds, $add);

        $this->command?->info('Seeder penggunaan dana selesai untuk tahun '.$this->tahun.'.');
    }

    private function siapkanRekening(): void
    {
        $daftar = [
            // Pendapatan
            ['4', 'Pendapatan', null, 'pendapatan'],
            ['4.2', 'Pendapatan Transfer', '4', 'pendapatan'],
            ['4.2.1', 'Dana Desa', '4.2', 'pendapatan'],
            ['4.2.2', 'Alokasi Dana Desa', '4.2', 'pendapatan'],

            // Belanja
            ['5', 'Belanja', null, 'belanja'],
            ['5.1', 'Belanja Pegawai', '5', 'belanja'],
            ['5.1.1', 'Belanja Penghasilan Tetap dan Tunjangan', '5.1', 'belanja'],
            ['5.1.1.01', 'Belanja Penghasilan Tetap Kepala Desa dan Perangkat Desa', '5.1.1', 'belanja'],
            ['5.1.1.02', 'Belanja Tunjangan Kepala Desa dan Perangkat Desa', '5.1.1', 'belanja'],
            ['5.2', 'Belanja Barang dan Jasa', '5', 'belanja'],
            ['5.2.1', 'Belanja Bahan/Material', '5.2', 'belanja'],
            ['5.2.1.01', 'Belanja Bahan/Material', '5.2.1', 'belanja'],
            ['5.2.2', 'Belanja Jasa Honorarium', '5.2', 'belanja'],
            ['5.2.2.03', 'Honorarium/Insentif Pelayanan Desa', '5.2.2', 'belanja'],
            ['5.2.3', 'Belanja Perjalanan Dinas', '5.2', 'belanja'],
            ['5.2.3.01', 'Belanja Perjalanan Dinas Dalam Kabupaten/Kota', '5.2.3', 'belanja'],
            ['5.2.4', 'Belanja Jasa Sewa', '5.2', 'belanja'],
            ['5.2.4.03', 'Belanja Jasa Sewa Sarana Mobilitas', '5.2.4', 'belanja'],
            ['5.2.5', 'Belanja Operasional Perkantoran', '5.2', 'belanja'],
            ['5.2.5.01', 'Belanja Alat Tulis Kantor', '5.2.5', 'belanja'],
            ['5.3', 'Belanja Modal', '5', 'belanja'],
            ['5.3.1', 'Belanja Modal Peralatan dan Mesin', '5.3', 'belanja'],
            ['5.3.1.01', 'Belanja Modal Peralatan dan Mesin', '5.3.1', 'belanja'],
            ['5.3.2', 'Belanja Modal Gedung dan Bangunan', '5.3', 'belanja'],
            ['5.3.2.01', 'Belanja Modal Gedung dan Bangunan', '5.3.2', 'belanja'],
            ['5.3.3', 'Belanja Modal Jalan, Irigasi dan Jaringan', '5.3', 'belanja'],
            ['5.3.3.01', 'Belanja Modal Jalan', '5.3.3', 'belanja'],
            ['5.4', 'Belanja Tidak Terduga', '5', 'belanja'],
            ['5.4.1', 'Belanja Tidak Terduga', '5.4', 'belanja'],
            ['5.4.1.01', 'Belanja Tidak Terduga', '5.4.1', 'belanja'],

            // Pembiayaan
            ['6', 'Pembiayaan', null, 'pembiayaan'],
            ['6.1', 'Penerimaan Pembiayaan', '6', 'pembiayaan_penerimaan'],
            ['6.1.1', 'SILPA Tahun Sebelumnya', '6.1', 'pembiayaan_penerimaan'],
            ['6.2', 'Pengeluaran Pembiayaan', '6', 'pembiayaan_pengeluaran'],
            ['6.2.1', 'Pembentukan Dana Cadangan', '6.2', 'pembiayaan_pengeluaran'],
            ['6.2.2', 'Penyertaan Modal Desa', '6.2', 'pembiayaan_pengeluaran'],
        ];

        foreach ($daftar as [$kode, $nama, $parent, $jenis]) {
            RekeningAnggaran::updateOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'jenis' => $jenis,
                    'parent_id' => $parent ? RekeningAnggaran::where('kode', $parent)->value('id') : null,
                    'level' => substr_count($kode, '.') + 1,
                    'urutan' => (int) str_replace('.', '', $kode),
                    'aktif' => true,
                ]
            );
        }
    }

    private function seedPemasukan(SumberDana $dds, SumberDana $add): void
    {
        $pemasukan = [
            [
                'sumber' => $dds,
                'kode_rekening' => '4.2.1',
                'nama' => 'Dana Desa (Dropping APBN)',
                'anggaran' => 900000000,
                'realisasi' => 810000000,
                'tanggal' => $this->tanggal(9, 25),
                'keterangan' => 'Akumulasi pencairan Dana Desa Tahap I-III',
            ],
            [
                'sumber' => $add,
                'kode_rekening' => '4.2.2',
                'nama' => 'Alokasi Dana Desa (APBD)',
                'anggaran' => 450000000,
                'realisasi' => 360000000,
                'tanggal' => $this->tanggal(7, 15),
                'keterangan' => 'Akumulasi pencairan Alokasi Dana Desa Tahap I-II',
            ],
        ];

        foreach ($pemasukan as $data) {
            Pemasukan::updateOrCreate(
                [
                    'sumber_dana_id' => $data['sumber']->id,
                    'rekening_anggaran_id' => $this->rekeningId($data['kode_rekening']),
                    'tanggal' => $data['tanggal'],
                ],
                [
                    'sumber_dana' => $data['nama'],
                    'jumlah' => $data['realisasi'],
                    'anggaran' => $data['anggaran'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        }
    }

    private function seedKegiatanDds(SumberDana $dds): void
    {
        $daftar = [
            [
                'nama' => 'Penyelenggaraan Belanja Penghasilan Tetap dan Tunjangan Perangkat Desa',
                'kode' => '01.01.01',
                'sub_bidang' => '01.01',
                'rencana' => 280000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.1.1.01',
                        'uraian' => 'Belanja Penghasilan Tetap Kepala Desa dan Perangkat Desa',
                        'anggaran' => 180000000,
                        'pengeluaran' => [
                            [3, 15, 90000000, 'Pembayaran penghasilan tetap Januari-Maret'],
                            [7, 10, 60000000, 'Pembayaran penghasilan tetap April-Juni'],
                        ],
                    ],
                    [
                        'kode' => '5.1.1.02',
                        'uraian' => 'Belanja Tunjangan Kepala Desa dan Perangkat Desa',
                        'anggaran' => 60000000,
                        'pengeluaran' => [
                            [8, 5, 45000000, 'Pembayaran tunjangan perangkat desa'],
                        ],
                    ],
                    [
                        'kode' => '5.2.2.03',
                        'uraian' => 'Honorarium/Insentif Pelayanan Desa',
                        'anggaran' => 40000000,
                        'pengeluaran' => [
                            [9, 10, 35000000, 'Honorarium pelayanan administrasi desa'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Penyediaan Sarana dan Prasarana Pemerintahan Desa',
                'kode' => '01.02.01',
                'sub_bidang' => '01.02',
                'rencana' => 110000000,
                'status' => StatusKegiatan::SELESAI,
                'belanja' => [
                    [
                        'kode' => '5.2.5.01',
                        'uraian' => 'Belanja Alat Tulis Kantor',
                        'anggaran' => 20000000,
                        'pengeluaran' => [
                            [2, 10, 15000000, 'Pengadaan alat tulis dan perlengkapan kantor'],
                        ],
                    ],
                    [
                        'kode' => '5.3.1.01',
                        'uraian' => 'Belanja Modal Peralatan dan Mesin',
                        'anggaran' => 60000000,
                        'pengeluaran' => [
                            [4, 18, 55000000, 'Pengadaan laptop dan printer kantor desa'],
                        ],
                    ],
                    [
                        'kode' => '5.2.4.03',
                        'uraian' => 'Belanja Jasa Sewa Sarana Mobilitas',
                        'anggaran' => 30000000,
                        'pengeluaran' => [
                            [6, 12, 20000000, 'Sewa kendaraan operasional desa'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Pembangunan Posyandu dan Pelayanan Kesehatan Desa',
                'kode' => '02.02.01',
                'sub_bidang' => '02.02',
                'rencana' => 220000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.2.1.01',
                        'uraian' => 'Belanja Bahan/Material',
                        'anggaran' => 120000000,
                        'pengeluaran' => [
                            [5, 20, 100000000, 'Pembelian material pembangunan posyandu'],
                        ],
                    ],
                    [
                        'kode' => '5.3.2.01',
                        'uraian' => 'Belanja Modal Gedung dan Bangunan',
                        'anggaran' => 100000000,
                        'pengeluaran' => [
                            [9, 30, 80000000, 'Pekerjaan struktur dan atap gedung posyandu'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Pembangunan Jalan Usaha Tani',
                'kode' => '02.03.01',
                'sub_bidang' => '02.03',
                'rencana' => 140000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.3.3.01',
                        'uraian' => 'Belanja Modal Jalan',
                        'anggaran' => 140000000,
                        'pengeluaran' => [
                            [10, 5, 100000000, 'Pengerasan dan perbaikan jalan usaha tani'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Pelatihan Pertanian Organik bagi Kelompok Tani',
                'kode' => '04.02.01',
                'sub_bidang' => '04.02',
                'rencana' => 80000000,
                'status' => StatusKegiatan::SELESAI,
                'belanja' => [
                    [
                        'kode' => '5.2.3.01',
                        'uraian' => 'Belanja Perjalanan Dinas Dalam Kabupaten/Kota',
                        'anggaran' => 80000000,
                        'pengeluaran' => [
                            [7, 22, 35000000, 'Perjalanan dinas pendampingan kelompok tani'],
                            [8, 12, 25000000, 'Perjalanan dinas pelatihan pertanian organik'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Penanggulangan Bencana Alam dan Keadaan Darurat',
                'kode' => '05.01.01',
                'sub_bidang' => '05.01',
                'rencana' => 50000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.4.1.01',
                        'uraian' => 'Belanja Tidak Terduga',
                        'anggaran' => 50000000,
                        'pengeluaran' => [
                            [10, 20, 20000000, 'Bantuan darurat bencana banjir'],
                        ],
                    ],
                ],
            ],
        ];

        $this->buatKegiatan($dds, $daftar);
    }

    private function seedKegiatanAdd(SumberDana $add): void
    {
        $daftar = [
            [
                'nama' => 'Penyelenggaraan Operasional Pemerintahan Desa',
                'kode' => null,
                'sub_bidang' => null,
                'rencana' => 180000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.2.5.01',
                        'uraian' => 'Belanja Alat Tulis Kantor',
                        'anggaran' => 30000000,
                        'pengeluaran' => [
                            [1, 15, 24000000, 'Pengadaan alat tulis kantor tahun '.$this->tahun],
                        ],
                    ],
                    [
                        'kode' => '5.2.4.03',
                        'uraian' => 'Belanja Jasa Sewa Sarana Mobilitas',
                        'anggaran' => 20000000,
                        'pengeluaran' => [
                            [3, 8, 12000000, 'Sewa kendaraan operasional pemerintahan desa'],
                        ],
                    ],
                    [
                        'kode' => '5.2.2.03',
                        'uraian' => 'Honorarium/Insentif Pelayanan Desa',
                        'anggaran' => 100000000,
                        'pengeluaran' => [
                            [6, 30, 85000000, 'Insentif aparatur dan pelayanan desa'],
                        ],
                    ],
                    [
                        'kode' => '5.3.1.01',
                        'uraian' => 'Belanja Modal Peralatan dan Mesin',
                        'anggaran' => 30000000,
                        'pengeluaran' => [
                            [4, 10, 20000000, 'Pengadaan meja dan kursi kerja perangkat desa'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Peningkatan Kapasitas Aparatur Desa',
                'kode' => null,
                'sub_bidang' => null,
                'rencana' => 100000000,
                'status' => StatusKegiatan::SELESAI,
                'belanja' => [
                    [
                        'kode' => '5.2.2.03',
                        'uraian' => 'Honorarium/Insentif Pelayanan Desa',
                        'anggaran' => 40000000,
                        'pengeluaran' => [
                            [5, 18, 30000000, 'Honorarium kegiatan peningkatan kapasitas aparatur'],
                        ],
                    ],
                    [
                        'kode' => '5.2.3.01',
                        'uraian' => 'Belanja Perjalanan Dinas Dalam Kabupaten/Kota',
                        'anggaran' => 60000000,
                        'pengeluaran' => [
                            [8, 25, 45000000, 'Perjalanan dinas bimbingan teknis aparatur desa'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Pemberdayaan Perempuan dan Perlindungan Anak',
                'kode' => null,
                'sub_bidang' => null,
                'rencana' => 100000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.2.1.01',
                        'uraian' => 'Belanja Bahan/Material',
                        'anggaran' => 50000000,
                        'pengeluaran' => [
                            [7, 8, 40000000, 'Pengadaan bahan pelatihan pemberdayaan perempuan'],
                        ],
                    ],
                    [
                        'kode' => '5.2.2.03',
                        'uraian' => 'Honorarium/Insentif Pelayanan Desa',
                        'anggaran' => 50000000,
                        'pengeluaran' => [
                            [9, 14, 35000000, 'Honorarium pendamping kegiatan perempuan dan anak'],
                        ],
                    ],
                ],
            ],
            [
                'nama' => 'Penanggulangan Keadaan Darurat Desa',
                'kode' => null,
                'sub_bidang' => null,
                'rencana' => 50000000,
                'status' => StatusKegiatan::BERJALAN,
                'belanja' => [
                    [
                        'kode' => '5.4.1.01',
                        'uraian' => 'Belanja Tidak Terduga',
                        'anggaran' => 50000000,
                        'pengeluaran' => [
                            [10, 28, 20000000, 'Penanganan keadaan darurat desa'],
                        ],
                    ],
                ],
            ],
        ];

        $this->buatKegiatan($add, $daftar);
    }

    private function buatKegiatan(SumberDana $sumberDana, array $daftar): void
    {
        foreach ($daftar as $data) {
            $subBidangId = null;

            if ($data['sub_bidang']) {
                $subBidangId = SubBidangKegiatan::where('kode', $data['sub_bidang'])->value('id');
            }

            $kegiatan = Kegiatan::updateOrCreate(
                [
                    'nama_kegiatan' => $data['nama'],
                    'sumber_dana_id' => $sumberDana->id,
                ],
                [
                    'kode_rekening' => $data['kode'],
                    'lokasi' => 'Desa Waindawula',
                    'sub_bidang_kegiatan_id' => $subBidangId,
                    'rencana_anggaran' => $data['rencana'],
                    'status' => $data['status']->value,
                ]
            );

            $totalRealisasi = 0;
            $kodeBelanja = collect($data['belanja'])->pluck('kode')->all();

            Pengeluaran::where('id_kegiatan', $kegiatan->id)->delete();

            $detailUsang = KegiatanBelanja::where('kegiatan_id', $kegiatan->id)
                ->whereNotIn('kode_rekening', $kodeBelanja)
                ->get();

            foreach ($detailUsang as $detail) {
                Pengeluaran::where('kegiatan_belanja_id', $detail->id)->delete();
                $detail->delete();
            }

            foreach ($data['belanja'] as $belanja) {
                $rekeningId = $this->rekeningId($belanja['kode']);

                $detail = KegiatanBelanja::updateOrCreate(
                    [
                        'kegiatan_id' => $kegiatan->id,
                        'kode_rekening' => $belanja['kode'],
                        'uraian' => $belanja['uraian'],
                    ],
                    [
                        'rekening_anggaran_id' => $rekeningId,
                        'anggaran' => $belanja['anggaran'],
                    ]
                );

                foreach ($belanja['pengeluaran'] as [$bulan, $hari, $jumlah, $keterangan]) {
                    Pengeluaran::updateOrCreate(
                        [
                            'sumber_dana_id' => $sumberDana->id,
                            'kegiatan_belanja_id' => $detail->id,
                            'tanggal' => $this->tanggal($bulan, $hari),
                            'jumlah' => $jumlah,
                        ],
                        [
                            'id_kegiatan' => $kegiatan->id,
                            'rekening_anggaran_id' => $rekeningId,
                            'keterangan' => $keterangan,
                        ]
                    );

                    $totalRealisasi += $jumlah;
                }
            }

            $kegiatan->update(['realisasi_anggaran' => $totalRealisasi]);
        }
    }

    private function seedPembiayaan(SumberDana $dds, SumberDana $add): void
    {
        $daftar = [
            [
                'sumber' => $dds,
                'jenis' => 'penerimaan',
                'kode_rekening' => '6.1.1',
                'uraian' => 'SILPA Tahun Anggaran Sebelumnya',
                'anggaran' => 25000000,
                'realisasi' => 25000000,
                'tanggal' => $this->tanggal(1, 5),
            ],
            [
                'sumber' => $dds,
                'jenis' => 'pengeluaran',
                'kode_rekening' => '6.2.2',
                'uraian' => 'Penyertaan Modal Desa pada BUMDes',
                'anggaran' => 15000000,
                'realisasi' => 15000000,
                'tanggal' => $this->tanggal(5, 10),
            ],
            [
                'sumber' => $add,
                'jenis' => 'penerimaan',
                'kode_rekening' => '6.1.1',
                'uraian' => 'SILPA Tahun Anggaran Sebelumnya',
                'anggaran' => 10000000,
                'realisasi' => 10000000,
                'tanggal' => $this->tanggal(1, 5),
            ],
            [
                'sumber' => $add,
                'jenis' => 'pengeluaran',
                'kode_rekening' => '6.2.1',
                'uraian' => 'Pembentukan Dana Cadangan Desa',
                'anggaran' => 5000000,
                'realisasi' => 5000000,
                'tanggal' => $this->tanggal(6, 15),
            ],
        ];

        foreach ($daftar as $data) {
            Pembiayaan::updateOrCreate(
                [
                    'sumber_dana_id' => $data['sumber']->id,
                    'jenis' => $data['jenis'],
                    'uraian' => $data['uraian'],
                ],
                [
                    'rekening_anggaran_id' => $this->rekeningId($data['kode_rekening']),
                    'anggaran' => $data['anggaran'],
                    'realisasi' => $data['realisasi'],
                    'tanggal' => $data['tanggal'],
                    'keterangan' => 'Data contoh laporan realisasi APBDes',
                ]
            );
        }
    }

    private function seedInventaris(SumberDana $dds, SumberDana $add): void
    {
        $pengeluaranDds = Pengeluaran::where('sumber_dana_id', $dds->id)
            ->whereHas('kegiatanBelanja', fn ($query) => $query->where('kode_rekening', '5.3.1.01'))
            ->first();

        $pengeluaranAdd = Pengeluaran::where('sumber_dana_id', $add->id)
            ->whereHas('kegiatanBelanja', fn ($query) => $query->where('kode_rekening', '5.3.1.01'))
            ->first();

        $daftar = [
            [
                'kode_barang' => 'INV/'.$this->tahun.'/001',
                'nama_barang' => 'Laptop Lenovo ThinkPad E14',
                'jumlah' => 1,
                'tanggal_perolehan' => $this->tanggal(4, 18),
                'nilai_aset' => 12000000,
                'kondisi' => KondisiInventaris::BAIK,
                'sumber' => $dds,
                'pengeluaran' => $pengeluaranDds,
                'keterangan' => 'Peralatan kantor desa',
            ],
            [
                'kode_barang' => 'INV/'.$this->tahun.'/002',
                'nama_barang' => 'Printer Epson L3210',
                'jumlah' => 2,
                'tanggal_perolehan' => $this->tanggal(4, 18),
                'nilai_aset' => 4500000,
                'kondisi' => KondisiInventaris::BAIK,
                'sumber' => $dds,
                'pengeluaran' => $pengeluaranDds,
                'keterangan' => 'Peralatan kantor desa',
            ],
            [
                'kode_barang' => 'INV/'.$this->tahun.'/003',
                'nama_barang' => 'Meja Kerja Perangkat Desa',
                'jumlah' => 4,
                'tanggal_perolehan' => $this->tanggal(4, 10),
                'nilai_aset' => 6000000,
                'kondisi' => KondisiInventaris::BAIK,
                'sumber' => $add,
                'pengeluaran' => $pengeluaranAdd,
                'keterangan' => 'Meja kerja untuk perangkat desa',
            ],
        ];

        foreach ($daftar as $data) {
            Inventaris::updateOrCreate(
                ['kode_barang' => $data['kode_barang']],
                [
                    'nama_barang' => $data['nama_barang'],
                    'jumlah' => $data['jumlah'],
                    'tanggal_perolehan' => $data['tanggal_perolehan'],
                    'nilai_aset' => $data['nilai_aset'],
                    'kondisi' => $data['kondisi']->value,
                    'sumber_dana_id' => $data['sumber']->id,
                    'id_pengeluaran' => $data['pengeluaran']?->id,
                    'keterangan' => $data['keterangan'],
                ]
            );
        }
    }

    private function rekeningId(string $kode): ?int
    {
        return RekeningAnggaran::where('kode', $kode)->value('id');
    }

    private function tanggal(int $bulan, int $hari): string
    {
        return Carbon::create($this->tahun, $bulan, $hari)->toDateString();
    }
}
