<?php

namespace Database\Seeders;

use App\Models\RekeningAnggaran;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApbdesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['kode' => 'DDS', 'nama' => 'Dana Desa', 'keterangan' => 'Dana Desa, dropping APBN'],
            ['kode' => 'ADD', 'nama' => 'Alokasi Dana Desa'],
            ['kode' => 'DBH', 'nama' => 'Dana Bagi Hasil'],
            ['kode' => 'PAD', 'nama' => 'Pendapatan Asli Desa'],
            ['kode' => 'LAINNYA', 'nama' => 'Sumber Dana Lainnya'],
        ] as $data) {
            SumberDana::firstOrCreate(['kode' => $data['kode']], $data);
        }

        $records = [
            ['kode' => '4', 'nama' => 'Pendapatan', 'jenis' => 'pendapatan'], ['kode' => '4.2', 'nama' => 'Pendapatan Transfer', 'jenis' => 'pendapatan', 'parent' => '4'], ['kode' => '4.2.1', 'nama' => 'Dana Desa', 'jenis' => 'pendapatan', 'parent' => '4.2'],
            ['kode' => '5', 'nama' => 'Belanja', 'jenis' => 'belanja'], ['kode' => '5.1', 'nama' => 'Belanja Pegawai', 'jenis' => 'belanja', 'parent' => '5'], ['kode' => '5.2', 'nama' => 'Belanja Barang dan Jasa', 'jenis' => 'belanja', 'parent' => '5'], ['kode' => '5.2.2', 'nama' => 'Belanja Jasa Honorarium', 'jenis' => 'belanja', 'parent' => '5.2'], ['kode' => '5.2.2.03', 'nama' => 'Honorarium/Insentif Pelayanan Desa', 'jenis' => 'belanja', 'parent' => '5.2.2'],
            ['kode' => '6', 'nama' => 'Pembiayaan', 'jenis' => 'pembiayaan'], ['kode' => '6.1', 'nama' => 'Penerimaan Pembiayaan', 'jenis' => 'pembiayaan_penerimaan', 'parent' => '6'], ['kode' => '6.1.1', 'nama' => 'SILPA Tahun Sebelumnya', 'jenis' => 'pembiayaan_penerimaan', 'parent' => '6.1'], ['kode' => '6.2', 'nama' => 'Pengeluaran Pembiayaan', 'jenis' => 'pembiayaan_pengeluaran', 'parent' => '6'], ['kode' => '6.2.1', 'nama' => 'Pembentukan Dana Cadangan', 'jenis' => 'pembiayaan_pengeluaran', 'parent' => '6.2'], ['kode' => '6.2.2', 'nama' => 'Penyertaan Modal Desa', 'jenis' => 'pembiayaan_pengeluaran', 'parent' => '6.2'],
        ];
        foreach ($records as $record) {
            $parent = isset($record['parent']) ? RekeningAnggaran::where('kode', $record['parent'])->first()?->id : null;
            RekeningAnggaran::updateOrCreate(['kode' => $record['kode']], ['nama' => $record['nama'], 'jenis' => $record['jenis'], 'parent_id' => $parent, 'level' => substr_count($record['kode'], '.') + 1, 'urutan' => (int) str_replace('.', '', $record['kode'])]);
        }

        $dds = SumberDana::where('kode', 'DDS')->value('id');
        $pendapatan = RekeningAnggaran::where('kode', '4.2.1')->value('id');

        if ($dds) {
            DB::table('pemasukan')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
            DB::table('pengeluaran')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
            DB::table('kegiatan')->whereNull('sumber_dana_id')->update(['sumber_dana_id' => $dds]);
        }

        if ($pendapatan) {
            DB::table('pemasukan')->whereNull('rekening_anggaran_id')->update(['rekening_anggaran_id' => $pendapatan]);
        }
    }
}
