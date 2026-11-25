<?php

use App\Livewire\Admin\KegiatanManagement;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SumberDana;
use Livewire\Livewire;

beforeEach(function () {
    $this->sumberDana = SumberDana::firstOrCreate(
        ['kode' => SumberDana::KODE_ALOKASI_DANA_DESA],
        ['nama' => 'Alokasi Dana Desa']
    );

    $this->rekening = RekeningAnggaran::firstOrCreate(
        ['kode' => '5.2.2.03'],
        [
            'nama' => 'Belanja Jasa Honorarium',
            'jenis' => 'belanja',
            'level' => 3,
            'urutan' => 52203,
            'aktif' => true,
        ]
    );

    Pemasukan::create([
        'sumber_dana' => 'Alokasi Dana Desa',
        'sumber_dana_id' => $this->sumberDana->id,
        'jumlah' => 100000000,
        'tanggal' => '2026-11-25',
    ]);
});

it('menyimpan kegiatan meski total detail berbeda dengan rencana anggaran', function () {
    Livewire::test(KegiatanManagement::class)
        ->set('dana', SumberDana::KODE_ALOKASI_DANA_DESA)
        ->set('nama_kegiatan', 'Penyelenggaraan Operasional Desa')
        ->set('lokasi', 'Kantor Desa')
        ->set('rencana_anggaran', '20000000')
        ->set('detailBelanja.0.rekening_id', (string) $this->rekening->id)
        ->set('detailBelanja.0.uraian', 'Pembelian Barang dan Jasa')
        ->set('detailBelanja.0.anggaran', '13000000')
        ->call('save')
        ->assertHasNoErrors();

    $kegiatan = Kegiatan::where('nama_kegiatan', 'Penyelenggaraan Operasional Desa')->firstOrFail();

    expect((float) $kegiatan->rencana_anggaran)->toBe(20000000.0)
        ->and((float) $kegiatan->belanjaDetails()->first()->anggaran)->toBe(13000000.0);
});

it('menampilkan realisasi per obyek detail di modal detail kegiatan', function () {
    $kegiatan = Kegiatan::create([
        'nama_kegiatan' => 'Penyelenggaraan Operasional Desa',
        'lokasi' => 'Kantor Desa',
        'rencana_anggaran' => 20000000,
        'status' => 'berjalan',
        'sumber_dana_id' => $this->sumberDana->id,
    ]);

    $detail = $kegiatan->belanjaDetails()->create([
        'rekening_anggaran_id' => $this->rekening->id,
        'kode_rekening' => $this->rekening->kode,
        'uraian' => 'Pembelian Barang dan Jasa',
        'anggaran' => 13000000,
    ]);

    Pengeluaran::create([
        'jumlah' => 5000000,
        'tanggal' => '2026-11-25',
        'keterangan' => 'Pembelian barang',
        'id_kegiatan' => $kegiatan->id,
        'kegiatan_belanja_id' => $detail->id,
        'sumber_dana_id' => $this->sumberDana->id,
        'rekening_anggaran_id' => $this->rekening->id,
    ]);

    Livewire::test(KegiatanManagement::class)
        ->set('dana', SumberDana::KODE_ALOKASI_DANA_DESA)
        ->call('openDetailModal', $kegiatan->id)
        ->assertSee('Rp 5.000.000')
        ->assertSee('Rp 13.000.000')
        ->assertSee('Rp 8.000.000');
});

it('mempertahankan tautan pengeluaran ketika detail kegiatan diedit', function () {
    $kegiatan = Kegiatan::create([
        'nama_kegiatan' => 'Penyelenggaraan Operasional Desa',
        'lokasi' => 'Kantor Desa',
        'rencana_anggaran' => 20000000,
        'status' => 'perencanaan',
        'sumber_dana_id' => $this->sumberDana->id,
    ]);

    $detail = $kegiatan->belanjaDetails()->create([
        'rekening_anggaran_id' => $this->rekening->id,
        'kode_rekening' => $this->rekening->kode,
        'uraian' => 'Pembelian Barang dan Jasa',
        'anggaran' => 13000000,
    ]);

    $pengeluaran = Pengeluaran::create([
        'jumlah' => 5000000,
        'tanggal' => '2026-11-25',
        'keterangan' => 'Pembelian barang',
        'id_kegiatan' => $kegiatan->id,
        'kegiatan_belanja_id' => $detail->id,
        'sumber_dana_id' => $this->sumberDana->id,
        'rekening_anggaran_id' => $this->rekening->id,
    ]);

    Livewire::test(KegiatanManagement::class)
        ->set('dana', SumberDana::KODE_ALOKASI_DANA_DESA)
        ->call('openEditModal', $kegiatan->id)
        ->set('detailBelanja.0.anggaran', '14000000')
        ->call('save')
        ->assertHasNoErrors();

    expect($pengeluaran->fresh()->kegiatan_belanja_id)->toBe($detail->id)
        ->and((float) $detail->fresh()->anggaran)->toBe(14000000.0);
});

it('mencatat otomatis maksimal total detail ketika rencana anggaran lebih besar', function () {
    Livewire::test(KegiatanManagement::class)
        ->set('dana', SumberDana::KODE_ALOKASI_DANA_DESA)
        ->set('nama_kegiatan', 'Kegiatan Selesai')
        ->set('lokasi', 'Kantor Desa')
        ->set('rencana_anggaran', '20000000')
        ->set('status', 'selesai')
        ->set('detailBelanja.0.rekening_id', (string) $this->rekening->id)
        ->set('detailBelanja.0.uraian', 'Pembelian Barang dan Jasa')
        ->set('detailBelanja.0.anggaran', '13000000')
        ->call('save')
        ->assertHasNoErrors();

    $kegiatan = Kegiatan::where('nama_kegiatan', 'Kegiatan Selesai')->firstOrFail();

    expect((float) $kegiatan->pengeluarans()->sum('jumlah'))->toBe(13000000.0);
});
