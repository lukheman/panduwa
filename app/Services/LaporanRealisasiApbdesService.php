<?php

namespace App\Services;

use App\Enums\StatusKegiatan;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pembiayaan;
use App\Models\Pengeluaran;
use Illuminate\Support\Collection;

class LaporanRealisasiApbdesService
{
    public function build(int $tahun, string $sampaiTanggal, ?int $sumberDanaId = null): array
    {
        $pendapatanQuery = $this->periode(Pemasukan::query()->with('sumberDana'), $tahun, $sampaiTanggal)
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId));
        $pendapatan = (clone $pendapatanQuery)->get();

        $pengeluaranQuery = $this->periode(Pengeluaran::query(), $tahun, $sampaiTanggal)
            ->where(function ($query) {
                $query->whereNull('id_kegiatan')
                    ->orWhereHas('kegiatan', fn ($kegiatan) => $kegiatan->where('status', '!=', StatusKegiatan::PERENCANAAN->value));
            })
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId));
        $pengeluaran = (clone $pengeluaranQuery)->get();

        $kegiatans = Kegiatan::query()
            ->with(['subBidang.bidang', 'belanjaDetails.rekening.parent.parent.parent'])
            ->where('status', '!=', StatusKegiatan::PERENCANAAN->value)
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId))
            ->get();

        $belanjaRows = $this->belanjaRows($kegiatans, $pengeluaran);
        $belanja = $kegiatans->sum('rencana_anggaran');
        $realisasiBelanja = $pengeluaran->sum('jumlah');

        $pembiayaanPenerimaan = $this->pembiayaan('penerimaan', $tahun, $sampaiTanggal, $sumberDanaId);
        $pembiayaanPengeluaran = $this->pembiayaan('pengeluaran', $tahun, $sampaiTanggal, $sumberDanaId);
        $pendapatanAnggaran = $pendapatan->sum(fn ($item) => $item->anggaran ?? $item->jumlah);
        $pendapatanRealisasi = $pendapatan->sum('jumlah');
        $penerimaanAnggaran = $pembiayaanPenerimaan->sum('anggaran');
        $penerimaanRealisasi = $pembiayaanPenerimaan->sum('realisasi');
        $pengeluaranPembiayaanAnggaran = $pembiayaanPengeluaran->sum('anggaran');
        $pengeluaranPembiayaanRealisasi = $pembiayaanPengeluaran->sum('realisasi');
        $surplusAnggaran = $pendapatanAnggaran - $belanja;
        $surplusRealisasi = $pendapatanRealisasi - $realisasiBelanja;
        $nettoAnggaran = $penerimaanAnggaran - $pengeluaranPembiayaanAnggaran;
        $nettoRealisasi = $penerimaanRealisasi - $pengeluaranPembiayaanRealisasi;

        return [
            'pendapatanRows' => $this->pendapatanRows($pendapatan),
            'pendapatanAnggaran' => $pendapatanAnggaran,
            'pendapatanRealisasi' => $pendapatanRealisasi,
            'belanjaRows' => $belanjaRows,
            'belanjaAnggaran' => $belanja,
            'belanjaRealisasi' => $realisasiBelanja,
            'surplusAnggaran' => $surplusAnggaran,
            'surplusRealisasi' => $surplusRealisasi,
            'pembiayaanPenerimaan' => $pembiayaanPenerimaan,
            'pembiayaanPengeluaran' => $pembiayaanPengeluaran,
            'penerimaanAnggaran' => $penerimaanAnggaran,
            'penerimaanRealisasi' => $penerimaanRealisasi,
            'pengeluaranAnggaran' => $pengeluaranPembiayaanAnggaran,
            'pengeluaranRealisasi' => $pengeluaranPembiayaanRealisasi,
            'nettoAnggaran' => $nettoAnggaran,
            'nettoRealisasi' => $nettoRealisasi,
            'silpaAnggaran' => $surplusAnggaran + $nettoAnggaran,
            'silpaRealisasi' => $surplusRealisasi + $nettoRealisasi,
        ];
    }

    private function periode($query, int $tahun, string $sampaiTanggal)
    {
        return $query->whereYear('tanggal', $tahun)->whereDate('tanggal', '<=', $sampaiTanggal);
    }

    private function pembiayaan(string $jenis, int $tahun, string $sampaiTanggal, ?int $sumberDanaId): Collection
    {
        return $this->periode(Pembiayaan::query()->with('rekening'), $tahun, $sampaiTanggal)
            ->where('jenis', $jenis)
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId))
            ->get();
    }

    private function pendapatanRows(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $kodeSumber = $item->sumberDana?->kode;

            return [
                'code' => $item->rekening?->kode ?? ($kodeSumber === 'ADD' ? '4.2.2' : '4.2.1'),
                'description' => $item->rekening?->nama ?? ($item->keterangan ?: $item->sumber_dana),
                'level' => 2,
                'anggaran' => $item->anggaran ?? $item->jumlah,
                'realisasi' => $item->jumlah,
                'class' => '',
            ];
        });
    }

    private function belanjaRows(Collection $kegiatans, Collection $pengeluarans): Collection
    {
        $rows = collect();

        if ($kegiatans->isNotEmpty() && $kegiatans->every(fn ($kegiatan) => $kegiatan->subBidang === null)) {
            return $this->belanjaRowsTanpaBidang($kegiatans, $pengeluarans);
        }

        $kegiatans = $kegiatans->sortBy([
            ['subBidang.bidang.kode', 'asc'],
            ['subBidang.kode', 'asc'],
            ['kode_rekening', 'asc'],
        ]);

        foreach ($kegiatans->groupBy(fn ($kegiatan) => $kegiatan->subBidang?->bidang?->id ?: 'lainnya') as $bidangKegiatans) {
            $bidang = $bidangKegiatans->first()->subBidang?->bidang;
            $bidangAnggaran = $bidangKegiatans->sum('rencana_anggaran');
            $bidangRealisasi = $bidangKegiatans->sum(fn ($kegiatan) => $pengeluarans->where('id_kegiatan', $kegiatan->id)->sum('jumlah'));
            $rows->push([
                'code' => $bidang?->kode ?: '05',
                'description' => 'BIDANG '.strtoupper($bidang?->nama ?: 'LAINNYA'),
                'level' => 0,
                'anggaran' => (float) $bidangAnggaran,
                'realisasi' => (float) $bidangRealisasi,
                'class' => 'field',
            ]);

            foreach ($bidangKegiatans->groupBy(fn ($kegiatan) => $kegiatan->subBidang?->id ?: 'lainnya') as $subBidangKegiatans) {
                $subBidang = $subBidangKegiatans->first()->subBidang;
                $subAnggaran = $subBidangKegiatans->sum('rencana_anggaran');
                $subRealisasi = $subBidangKegiatans->sum(fn ($kegiatan) => $pengeluarans->where('id_kegiatan', $kegiatan->id)->sum('jumlah'));
                $rows->push([
                    'code' => $subBidang?->kode ?: ($bidang?->kode ?: '05').'.00',
                    'description' => $subBidang?->nama ?: 'Lainnya',
                    'level' => 1,
                    'anggaran' => (float) $subAnggaran,
                    'realisasi' => (float) $subRealisasi,
                    'class' => 'subfield',
                ]);

                foreach ($subBidangKegiatans as $kegiatan) {
                    $realisasi = $pengeluarans->where('id_kegiatan', $kegiatan->id)->sum('jumlah');
                    $code = $kegiatan->kode_rekening ?: ($kegiatan->subBidang?->kode ? $kegiatan->subBidang->kode.'.01' : '');
                    $rows->push([
                        'code' => $code,
                        'description' => $kegiatan->nama_kegiatan,
                        'level' => 2,
                        'anggaran' => (float) $kegiatan->rencana_anggaran,
                        'realisasi' => (float) $realisasi,
                        'class' => 'activity',
                    ]);

                    foreach ($kegiatan->belanjaDetails as $detail) {
                        $this->pushRekeningRows($rows, $detail, $pengeluarans);
                    }
                }
            }
        }

        $unassigned = $pengeluarans->whereNull('id_kegiatan');
        if ($unassigned->isNotEmpty()) {
            $rows->push([
                'code' => '5.9',
                'description' => 'Belanja lainnya',
                'level' => 1,
                'anggaran' => 0,
                'realisasi' => (float) $unassigned->sum('jumlah'),
                'class' => 'type',
            ]);
        }

        return $rows;
    }

    private function belanjaRowsTanpaBidang(Collection $kegiatans, Collection $pengeluarans): Collection
    {
        $rows = collect();

        foreach ($kegiatans->sortBy('nama_kegiatan') as $kegiatan) {
            $realisasi = $pengeluarans->where('id_kegiatan', $kegiatan->id)->sum('jumlah');
            $rows->push([
                'code' => $kegiatan->kode_rekening ?: '5.2',
                'description' => $kegiatan->nama_kegiatan,
                'level' => 0,
                'anggaran' => (float) $kegiatan->rencana_anggaran,
                'realisasi' => (float) $realisasi,
                'class' => 'activity',
            ]);

            foreach ($kegiatan->belanjaDetails as $detail) {
                $this->pushRekeningRows($rows, $detail, $pengeluarans);
            }
        }

        $unassigned = $pengeluarans->whereNull('id_kegiatan');
        if ($unassigned->isNotEmpty()) {
            $rows->push([
                'code' => '5.9',
                'description' => 'Belanja lainnya',
                'level' => 1,
                'anggaran' => 0,
                'realisasi' => (float) $unassigned->sum('jumlah'),
                'class' => 'type',
            ]);
        }

        return $rows;
    }

    private function pushRekeningRows(Collection $rows, $detail, Collection $pengeluarans): void
    {
        $rekening = $detail->rekening;
        $ancestors = collect();
        while ($rekening) {
            if ($rekening->kode !== '5') {
                $ancestors->prepend($rekening);
            }
            $rekening = $rekening->parent;
        }

        foreach ($ancestors as $index => $rekening) {
            $anggaran = $index === $ancestors->count() - 1 ? (float) $detail->anggaran : 0;
            $realisasi = $index === $ancestors->count() - 1
                    ? (float) $pengeluarans->where('kegiatan_belanja_id', $detail->id)->sum('jumlah')
                    : 0;
            $existing = $rows->search(fn ($row) => $row['code'] === $rekening->kode);

            if ($existing !== false) {
                $row = $rows->get($existing);
                $row['anggaran'] += $anggaran;
                $row['realisasi'] += $realisasi;
                $rows->put($existing, $row);
            } else {
                $rows->push([
                    'code' => $rekening->kode,
                    'description' => $rekening->nama,
                    'level' => min($index + 1, 3),
                    'anggaran' => $anggaran,
                    'realisasi' => $realisasi,
                    'class' => $index === 0 ? 'type' : ($index === 1 ? 'group' : ''),
                ]);
            }
        }

        if ($ancestors->isEmpty()) {
            $rows->push([
                'code' => $detail->kode_rekening ?: '',
                'description' => $detail->uraian,
                'level' => 3,
                'anggaran' => (float) $detail->anggaran,
                'realisasi' => (float) $pengeluarans->where('kegiatan_belanja_id', $detail->id)->sum('jumlah'),
                'class' => '',
            ]);
        }
    }
}
