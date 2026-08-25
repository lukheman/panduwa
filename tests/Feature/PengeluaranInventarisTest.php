<?php

use App\Enums\KondisiInventaris;
use App\Livewire\Admin\PengeluaranManagement;
use App\Models\Inventaris;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('links optional inventory to an expense through the existing foreign key', function () {
    $pengeluaranId = DB::table('pengeluaran')->insertGetId([
        'jumlah' => 500000,
        'tanggal' => '2026-08-25',
        'keterangan' => 'Pembelian meja',
        'id_kegiatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $inventarisId = DB::table('inventaris')->insertGetId([
        'kode_barang' => 'INV-TEST-001',
        'nama_barang' => 'Meja kerja',
        'tanggal_perolehan' => '2026-08-25',
        'nilai_aset' => 500000,
        'kondisi' => KondisiInventaris::BAIK->value,
        'id_pengeluaran' => $pengeluaranId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $pengeluaran = Pengeluaran::with('inventaris')->findOrFail($pengeluaranId);

    expect($pengeluaran->inventaris->is(Inventaris::findOrFail($inventarisId)))->toBeTrue();
});

it('allows expense without inventory relation', function () {
    $pengeluaranId = DB::table('pengeluaran')->insertGetId([
        'jumlah' => 100000,
        'tanggal' => '2026-08-25',
        'keterangan' => 'Biaya operasional',
        'id_kegiatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Pengeluaran::findOrFail($pengeluaranId)->inventaris)->toBeNull();
});

it('creates inventory details when expense is marked as inventory', function () {
    DB::table('pemasukan')->insert([
        'sumber_dana' => 'Dana Test',
        'jumlah' => 1000000,
        'tanggal' => '2026-08-25',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(PengeluaranManagement::class)
        ->set('tanggal', '2026-08-25')
        ->set('jumlah', '500000')
        ->set('keterangan', 'Pembelian bangku')
        ->set('catatSebagaiInventaris', true)
        ->set('kode_barang', 'INV-TEST-002')
        ->set('nama_barang', 'Bangku kayu')
        ->set('kondisi', KondisiInventaris::BAIK->value)
        ->call('save')
        ->assertHasNoErrors();

    $pengeluaran = Pengeluaran::with('inventaris')->where('keterangan', 'Pembelian bangku')->firstOrFail();

    expect($pengeluaran->inventaris)->not->toBeNull();
    expect($pengeluaran->inventaris->nama_barang)->toBe('Bangku kayu');
});
