<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Builder;

class RingkasanSumberDanaService
{
    /**
     * Ringkasan Dana Desa dan Alokasi Dana Desa untuk dashboard.
     *
     * @return array<string, array{kode: string, nama: string, pemasukan: float, pengeluaran: float, saldo: float, anggaran: float}>
     */
    public function build(): array
    {
        $sumberDanas = SumberDana::query()
            ->whereIn('kode', [SumberDana::KODE_DANA_DESA, SumberDana::KODE_ALOKASI_DANA_DESA])
            ->get()
            ->keyBy('kode');

        $ringkasan = [];

        foreach ([SumberDana::KODE_DANA_DESA, SumberDana::KODE_ALOKASI_DANA_DESA] as $kode) {
            $sumberDana = $sumberDanas->get($kode);
            $sumberDanaId = $sumberDana?->id;

            $pemasukan = $this->query(Pemasukan::query(), $sumberDanaId)->sum('jumlah');
            $pengeluaran = $this->query(Pengeluaran::query(), $sumberDanaId)->sum('jumlah');
            $anggaran = $this->query(Kegiatan::query(), $sumberDanaId)->sum('rencana_anggaran');

            $ringkasan[$kode] = [
                'kode' => $kode,
                'nama' => $sumberDana?->nama ?? SumberDana::labelUntukKode($kode),
                'pemasukan' => (float) $pemasukan,
                'pengeluaran' => (float) $pengeluaran,
                'saldo' => (float) $pemasukan - (float) $pengeluaran,
                'anggaran' => (float) $anggaran,
            ];
        }

        return $ringkasan;
    }

    private function query(Builder $query, ?int $sumberDanaId): Builder
    {
        if ($sumberDanaId) {
            return $query->untukSumberDana($sumberDanaId);
        }

        return $query->whereRaw('1 = 0');
    }
}
