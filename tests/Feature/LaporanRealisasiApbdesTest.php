<?php

use App\Enums\StatusKegiatan;
use App\Models\BidangKegiatan;
use App\Models\Kegiatan;
use App\Models\KegiatanBelanja;
use App\Models\Pemasukan;
use App\Models\Pembiayaan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SubBidangKegiatan;
use App\Models\SumberDana;
use App\Services\LaporanRealisasiApbdesService;

function rekeningLaporan(string $kode, string $nama, ?string $parent = null): RekeningAnggaran
{
    return RekeningAnggaran::updateOrCreate(['kode' => $kode], [
        'nama' => $nama,
        'jenis' => 'belanja',
        'parent_id' => $parent ? RekeningAnggaran::where('kode', $parent)->value('id') : null,
        'level' => substr_count($kode, '.') + 1,
    ]);
}

it('menyusun laporan per sumberdana sebagai pohon enam level belanja', function () {
    $dana = SumberDana::firstOrCreate(['kode' => 'DDS'], ['nama' => 'Dana Desa']);

    rekeningLaporan('4', 'Pendapatan');
    rekeningLaporan('4.2', 'Pendapatan Transfer', '4');
    rekeningLaporan('4.2.1', 'Dana Desa', '4.2');

    rekeningLaporan('5', 'Belanja');
    rekeningLaporan('5.2', 'Belanja Barang dan Jasa', '5');
    rekeningLaporan('5.2.1', 'Belanja Barang Perlengkapan', '5.2');
    rekeningLaporan('5.2.1.05', 'Belanja Barang Cetak dan Penggandaan', '5.2.1');

    rekeningLaporan('6', 'Pembiayaan');
    rekeningLaporan('6.1', 'Penerimaan Pembiayaan', '6');
    rekeningLaporan('6.2', 'Pengeluaran Pembiayaan', '6');
    rekeningLaporan('6.2.2', 'Penyertaan Modal Desa', '6.2');

    $bidang = BidangKegiatan::firstOrCreate(['kode' => '02'], ['nama' => 'Pelaksanaan Pembangunan Desa']);
    $subBidang = SubBidangKegiatan::firstOrCreate([
        'bidang_kegiatan_id' => $bidang->id,
        'kode' => '02.02',
    ], ['nama' => 'Kesehatan']);

    $kegiatan = Kegiatan::create([
        'nama_kegiatan' => 'Penyelenggaraan Pos Kesehatan Desa',
        'kode_rekening' => '02.02.01',
        'sumber_dana_id' => $dana->id,
        'lokasi' => 'Desa Waindawula',
        'sub_bidang_kegiatan_id' => $subBidang->id,
        'rencana_anggaran' => 100000000,
        'status' => StatusKegiatan::BERJALAN,
    ]);

    $detail = KegiatanBelanja::create([
        'kegiatan_id' => $kegiatan->id,
        'rekening_anggaran_id' => RekeningAnggaran::where('kode', '5.2.1.05')->value('id'),
        'kode_rekening' => '5.2.1.05',
        'uraian' => 'Belanja Barang Cetak dan Penggandaan',
        'anggaran' => 100000000,
    ]);

    Pemasukan::create([
        'sumber_dana_id' => $dana->id,
        'rekening_anggaran_id' => RekeningAnggaran::where('kode', '4.2.1')->value('id'),
        'sumber_dana' => 'Dana Desa',
        'jumlah' => 120000000,
        'anggaran' => 200000000,
        'tanggal' => '2025-06-01',
    ]);

    Pengeluaran::create([
        'sumber_dana_id' => $dana->id,
        'rekening_anggaran_id' => $detail->rekening_anggaran_id,
        'jumlah' => 60000000,
        'tanggal' => '2025-07-01',
        'id_kegiatan' => $kegiatan->id,
        'kegiatan_belanja_id' => $detail->id,
    ]);

    Pembiayaan::create([
        'jenis' => 'pengeluaran',
        'rekening_anggaran_id' => RekeningAnggaran::where('kode', '6.2.2')->value('id'),
        'sumber_dana_id' => $dana->id,
        'uraian' => 'Penyertaan Modal Desa',
        'anggaran' => 10000000,
        'realisasi' => 30000000,
        'tanggal' => '2025-08-01',
    ]);

    $ringkasan = app(LaporanRealisasiApbdesService::class)->build(2025, '2025-12-31', $dana->id);
    $baris = collect($ringkasan['rows'])->keyBy(fn (array $row) => $row['gaya'].'|'.$row['kode']);

    expect($ringkasan['pendapatanAnggaran'])->toBe(200000000.0)
        ->and($ringkasan['pendapatanRealisasi'])->toBe(120000000.0)
        ->and($ringkasan['belanjaAnggaran'])->toBe(100000000.0)
        ->and($ringkasan['belanjaRealisasi'])->toBe(60000000.0)
        ->and($ringkasan['nettoRealisasi'])->toBe(-30000000.0)
        ->and($ringkasan['silpaRealisasi'])->toBe(30000000.0);

    expect($baris->get('bidang|02')['uraian'])->toBe('BIDANG PELAKSANAAN PEMBANGUNAN DESA')
        ->and($baris->get('sub_bidang|02.02')['uraian'])->toBe('Kesehatan')
        ->and($baris->get('kegiatan|02.02.01.')['uraian'])->toBe('Penyelenggaraan Pos Kesehatan Desa')
        ->and($baris->get('kelompok_belanja|5.2.')['anggaran'])->toBe(100000000.0)
        ->and($baris->get('jenis_belanja|5.2.1.')['realisasi'])->toBe(60000000.0)
        ->and($baris->get('obyek|5.2.1.05.')['lebih'])->toBe(40000000.0);

    expect(array_column([
        $baris->get('bidang|02'),
        $baris->get('sub_bidang|02.02'),
        $baris->get('kegiatan|02.02.01.'),
        $baris->get('kelompok_belanja|5.2.'),
        $baris->get('jenis_belanja|5.2.1.'),
        $baris->get('obyek|5.2.1.05.'),
    ], 'indent'))->toBe([0, 1, 2, 3, 4, 5]);
});

it('merender format angka Indonesia dan blok tanda tangan pada cetakan', function () {
    $ringkasan = [
        'rows' => [
            ['kode' => '4.', 'uraian' => 'PENDAPATAN', 'gaya' => 'akun', 'indent' => 0, 'anggaran' => null, 'realisasi' => null, 'lebih' => null],
            ['kode' => '', 'uraian' => 'PEMBIAYAAN NETTO', 'gaya' => 'total', 'indent' => 0, 'anggaran' => 0.0, 'realisasi' => -171713800.0, 'lebih' => 171713800.0],
        ],
    ];

    $html = view('pdf.laporan-realisasi-apbdes', [
        'tahun' => 2025,
        'ringkasan' => $ringkasan,
        'sampaiTanggal' => '2025-12-31',
        'sumberDana' => SumberDana::firstOrCreate(['kode' => 'DDS'], ['nama' => 'Dana Desa']),
        'namaDesa' => 'Waindawula',
        'namaKepalaDesa' => 'Nama Kades',
        'tanggalTandaTangan' => '31 Desember 2025',
        'dicetakPada' => '31/12/2025 10:00:00',
        'footerLabel' => 'Printed by PANDUWA',
    ])->render();

    expect($html)->toContain('(171.713.800,00)')
        ->and($html)->toContain('0,00')
        ->and($html)->toContain('Sumberdana')
        ->and($html)->toContain('DDS Dana Desa (Dropping APBN)')
        ->and($html)->toContain('Waindawula, 31 Desember 2025')
        ->and($html)->toContain('KEPALA DESA')
        ->and($html)->toContain('NAMA KADES')
        ->and($html)->toContain('Printed by PANDUWA');
});
