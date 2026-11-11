<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan master sumber dana dan rekening pendapatan tersedia,
     * termasuk rekening terpisah untuk Dana Desa (4.2.1) dan
     * Alokasi Dana Desa (4.2.2).
     */
    public function up(): void
    {
        $now = now();

        $sumberDana = [
            ['kode' => 'DDS', 'nama' => 'Dana Desa', 'keterangan' => 'Dana Desa, dropping APBN'],
            ['kode' => 'ADD', 'nama' => 'Alokasi Dana Desa', 'keterangan' => 'Alokasi Dana Desa, APBD'],
            ['kode' => 'DBH', 'nama' => 'Dana Bagi Hasil'],
            ['kode' => 'PAD', 'nama' => 'Pendapatan Asli Desa'],
            ['kode' => 'LAINNYA', 'nama' => 'Sumber Dana Lainnya'],
        ];

        foreach ($sumberDana as $data) {
            if (DB::table('sumber_dana')->where('kode', $data['kode'])->exists()) {
                continue;
            }

            DB::table('sumber_dana')->insert($data + [
                'aktif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $rekening = [
            ['kode' => '4', 'nama' => 'Pendapatan', 'jenis' => 'pendapatan'],
            ['kode' => '4.1', 'nama' => 'Pendapatan Asli Desa', 'jenis' => 'pendapatan', 'parent' => '4'],
            ['kode' => '4.1.1', 'nama' => 'Hasil Usaha Desa', 'jenis' => 'pendapatan', 'parent' => '4.1'],
            ['kode' => '4.1.2', 'nama' => 'Hasil Aset Desa', 'jenis' => 'pendapatan', 'parent' => '4.1'],
            ['kode' => '4.1.3', 'nama' => 'Swadaya, Partisipasi dan Gotong Royong', 'jenis' => 'pendapatan', 'parent' => '4.1'],
            ['kode' => '4.1.4', 'nama' => 'Lain-lain Pendapatan Asli Desa', 'jenis' => 'pendapatan', 'parent' => '4.1'],
            ['kode' => '4.2', 'nama' => 'Pendapatan Transfer', 'jenis' => 'pendapatan', 'parent' => '4'],
            ['kode' => '4.2.1', 'nama' => 'Dana Desa', 'jenis' => 'pendapatan', 'parent' => '4.2'],
            ['kode' => '4.2.2', 'nama' => 'Alokasi Dana Desa', 'jenis' => 'pendapatan', 'parent' => '4.2'],
            ['kode' => '4.2.3', 'nama' => 'Dana Bagi Hasil', 'jenis' => 'pendapatan', 'parent' => '4.2'],
            ['kode' => '4.2.4', 'nama' => 'Bantuan Keuangan', 'jenis' => 'pendapatan', 'parent' => '4.2'],
            ['kode' => '4.3', 'nama' => 'Lain-lain Pendapatan Desa yang Sah', 'jenis' => 'pendapatan', 'parent' => '4'],
            ['kode' => '4.3.1', 'nama' => 'Lain-lain Pendapatan Desa yang Sah', 'jenis' => 'pendapatan', 'parent' => '4.3'],
        ];

        foreach ($rekening as $record) {
            $parentId = isset($record['parent'])
                ? DB::table('rekening_anggaran')->where('kode', $record['parent'])->value('id')
                : null;

            $values = [
                'nama' => $record['nama'],
                'jenis' => $record['jenis'],
                'parent_id' => $parentId,
                'level' => substr_count($record['kode'], '.') + 1,
                'urutan' => (int) str_replace('.', '', $record['kode']),
                'aktif' => true,
                'updated_at' => $now,
            ];

            $exists = DB::table('rekening_anggaran')->where('kode', $record['kode'])->exists();

            if ($exists) {
                DB::table('rekening_anggaran')->where('kode', $record['kode'])->update($values);
            } else {
                DB::table('rekening_anggaran')->insert($values + [
                    'kode' => $record['kode'],
                    'created_at' => $now,
                ]);
            }
        }

        $this->syncRekeningPemasukan();
    }

    /**
     * Samakan sumber dana dan rekening pendapatan pada data pemasukan lama.
     */
    private function syncRekeningPemasukan(): void
    {
        $map = [
            'PAD' => '4.1.1',
            'DDS' => '4.2.1',
            'ADD' => '4.2.2',
            'DBH' => '4.2.3',
            'LAINNYA' => '4.3.1',
        ];

        $polaSumberDana = [
            'ADD' => ['%Alokasi Dana Desa%', '%ADD%'],
            'DDS' => ['%Dana Desa%', '%DDS%'],
            'DBH' => ['%Bagi Hasil%', '%DBH%'],
            'PAD' => ['%Pendapatan Asli Desa%', '%PAD%'],
        ];

        foreach ($polaSumberDana as $sumberKode => $polas) {
            $sumberId = DB::table('sumber_dana')->where('kode', $sumberKode)->value('id');

            if (! $sumberId) {
                continue;
            }

            foreach ($polas as $pola) {
                DB::table('pemasukan')
                    ->whereNull('sumber_dana_id')
                    ->where('sumber_dana', 'like', $pola)
                    ->update(['sumber_dana_id' => $sumberId]);
            }
        }

        foreach ($map as $sumberKode => $rekeningKode) {
            $sumberId = DB::table('sumber_dana')->where('kode', $sumberKode)->value('id');
            $rekeningId = DB::table('rekening_anggaran')->where('kode', $rekeningKode)->value('id');

            if (! $sumberId || ! $rekeningId) {
                continue;
            }

            DB::table('pemasukan')
                ->where('sumber_dana_id', $sumberId)
                ->update(['rekening_anggaran_id' => $rekeningId]);
        }
    }

    public function down(): void
    {
        // Master data tetap dipertahankan saat migrasi dibatalkan.
    }
};
