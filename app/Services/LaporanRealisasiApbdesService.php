<?php

namespace App\Services;

use App\Enums\StatusKegiatan;
use App\Models\BidangKegiatan;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pembiayaan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SubBidangKegiatan;
use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Collection;

class LaporanRealisasiApbdesService
{
    /**
     * Nama rekening cadangan bila master rekening belum lengkap.
     */
    private const NAMA_REKENING = [
        '4' => 'Pendapatan',
        '4.1' => 'Pendapatan Asli Desa',
        '4.2' => 'Pendapatan Transfer',
        '4.2.1' => 'Dana Desa',
        '4.2.2' => 'Alokasi Dana Desa',
        '4.2.3' => 'Dana Bagi Hasil',
        '4.2.4' => 'Bantuan Keuangan',
        '4.3' => 'Lain-lain Pendapatan Desa yang Sah',
        '5' => 'Belanja',
        '5.1' => 'Belanja Pegawai',
        '5.2' => 'Belanja Barang dan Jasa',
        '5.3' => 'Belanja Modal',
        '5.4' => 'Belanja Tidak Terduga',
        '6' => 'Pembiayaan',
        '6.1' => 'Penerimaan Pembiayaan',
        '6.1.1' => 'SILPA Tahun Sebelumnya',
        '6.1.4' => 'Penerimaan Kembali Penyertaan Modal',
        '6.2' => 'Pengeluaran Pembiayaan',
        '6.2.1' => 'Pembentukan Dana Cadangan',
        '6.2.2' => 'Penyertaan Modal Desa',
    ];

    /**
     * Susun seluruh baris laporan REALISASI APBDes per sumberdana.
     *
     * @return array<string, mixed>
     */
    public function build(int $tahun, string $sampaiTanggal, ?int $sumberDanaId = null): array
    {
        $pendapatanItems = $this->periode(Pemasukan::query()->with('rekening'), $tahun, $sampaiTanggal)
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId))
            ->get();

        $pengeluaran = $this->periode(Pengeluaran::query(), $tahun, $sampaiTanggal)
            ->where(function ($query): void {
                $query->whereNull('id_kegiatan')
                    ->orWhereHas('kegiatan', fn ($kegiatan) => $kegiatan->where('status', '!=', StatusKegiatan::PERENCANAAN->value));
            })
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId))
            ->get();

        $kegiatans = Kegiatan::query()
            ->with(['subBidang.bidang', 'belanjaDetails.rekening'])
            ->where('status', '!=', StatusKegiatan::PERENCANAAN->value)
            ->when($sumberDanaId, fn ($query) => $query->untukSumberDana($sumberDanaId))
            ->get();

        $penerimaan = $this->pembiayaan('penerimaan', $tahun, $sampaiTanggal, $sumberDanaId);
        $pengeluaranPembiayaan = $this->pembiayaan('pengeluaran', $tahun, $sampaiTanggal, $sumberDanaId);

        $masterRekening = RekeningAnggaran::query()->get();
        $bidangs = BidangKegiatan::query()->get();
        $subBidangs = SubBidangKegiatan::query()->get();
        $sumberDana = $sumberDanaId ? SumberDana::query()->find($sumberDanaId) : null;

        $pendapatanAnggaran = (float) $pendapatanItems->sum(fn ($item) => $item->anggaran ?? $item->jumlah);
        $pendapatanRealisasi = (float) $pendapatanItems->sum('jumlah');

        [$belanjaRows, $belanjaAnggaran, $belanjaRealisasi] = $this->belanjaRows(
            $kegiatans,
            $pengeluaran,
            $masterRekening,
            $bidangs,
            $subBidangs
        );

        $pendapatanRows = $this->pendapatanRows($pendapatanItems, $masterRekening, $sumberDana);
        $penerimaanRows = $this->pembiayaanRows($penerimaan, '6.1', 'Penerimaan Pembiayaan', 'kelompok', 1);
        $pengeluaranPembiayaanRows = $this->pembiayaanRows($pengeluaranPembiayaan, '6.2', 'Pengeluaran Pembiayaan', 'kelompok', 1);

        $penerimaanAnggaran = $penerimaan->sum('anggaran');
        $penerimaanRealisasi = $penerimaan->sum('realisasi');
        $pengeluaranPembiayaanAnggaran = $pengeluaranPembiayaan->sum('anggaran');
        $pengeluaranPembiayaanRealisasi = $pengeluaranPembiayaan->sum('realisasi');

        $surplusAnggaran = $pendapatanAnggaran - $belanjaAnggaran;
        $surplusRealisasi = $pendapatanRealisasi - $belanjaRealisasi;
        $nettoAnggaran = $penerimaanAnggaran - $pengeluaranPembiayaanAnggaran;
        $nettoRealisasi = $penerimaanRealisasi - $pengeluaranPembiayaanRealisasi;
        $silpaAnggaran = $surplusAnggaran + $nettoAnggaran;
        $silpaRealisasi = $surplusRealisasi + $nettoRealisasi;

        $rows = [];
        $rows[] = $this->headerRow('4.', 'PENDAPATAN');
        $rows = array_merge($rows, $pendapatanRows);
        $rows[] = $this->totalRow('JUMLAH PENDAPATAN', $pendapatanAnggaran, $pendapatanRealisasi);

        $rows[] = $this->headerRow('5.', 'BELANJA');
        $rows = array_merge($rows, $belanjaRows);
        $rows[] = $this->totalRow('JUMLAH BELANJA', $belanjaAnggaran, $belanjaRealisasi);
        $rows[] = $this->totalRow('SURPLUS / (DEFISIT)', $surplusAnggaran, $surplusRealisasi);

        $rows[] = $this->headerRow('6.', 'PEMBIAYAAN');
        $rows = array_merge($rows, $penerimaanRows, $pengeluaranPembiayaanRows);
        $rows[] = $this->totalRow('PEMBIAYAAN NETTO', $nettoAnggaran, $nettoRealisasi);
        $rows[] = $this->totalRow('SISA LEBIH / (KURANG) PERHITUNGAN ANGGARAN', $silpaAnggaran, $silpaRealisasi);

        return [
            'rows' => $rows,
            'pendapatanRows' => $pendapatanRows,
            'belanjaRows' => $belanjaRows,
            'pembiayaanPenerimaan' => $penerimaanRows,
            'pembiayaanPengeluaran' => $pengeluaranPembiayaanRows,
            'pendapatanAnggaran' => $pendapatanAnggaran,
            'pendapatanRealisasi' => $pendapatanRealisasi,
            'belanjaAnggaran' => $belanjaAnggaran,
            'belanjaRealisasi' => $belanjaRealisasi,
            'surplusAnggaran' => $surplusAnggaran,
            'surplusRealisasi' => $surplusRealisasi,
            'penerimaanAnggaran' => $penerimaanAnggaran,
            'penerimaanRealisasi' => $penerimaanRealisasi,
            'pengeluaranAnggaran' => $pengeluaranPembiayaanAnggaran,
            'pengeluaranRealisasi' => $pengeluaranPembiayaanRealisasi,
            'nettoAnggaran' => $nettoAnggaran,
            'nettoRealisasi' => $nettoRealisasi,
            'silpaAnggaran' => $silpaAnggaran,
            'silpaRealisasi' => $silpaRealisasi,
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

    /**
     * @return array{0: array, 1: float, 2: float}
     */
    private function belanjaRows(
        Collection $kegiatans,
        Collection $pengeluarans,
        Collection $masterRekening,
        Collection $bidangs,
        Collection $subBidangs
    ): array {
        $pengeluaranPerKegiatan = $pengeluarans->groupBy('id_kegiatan');
        $bidangNodes = [];

        foreach ($kegiatans->sortBy([
            ['subBidang.bidang.kode', 'asc'],
            ['subBidang.kode', 'asc'],
            ['kode_rekening', 'asc'],
        ]) as $kegiatan) {
            $leaves = $this->nilaiDaunBelanja(
                $kegiatan,
                $pengeluaranPerKegiatan->get($kegiatan->id, collect()),
                $masterRekening
            );

            [$bidangKode, $bidangNama, $subKode, $subNama] = $this->lokasiKegiatan($kegiatan, $bidangs, $subBidangs);

            $children = $this->rekeningTree($leaves, $masterRekening);
            $kegiatanNode = [
                'kode' => $this->tampilKode((string) ($kegiatan->kode_rekening ?: $subKode.'.01')),
                'uraian' => (string) $kegiatan->nama_kegiatan,
                'gaya' => 'kegiatan',
                'indent' => 2,
                'children' => $children,
                'anggaran' => $children === [] ? (float) $kegiatan->rencana_anggaran : 0.0,
                'realisasi' => $children === [] ? (float) $pengeluaranPerKegiatan->get($kegiatan->id, collect())->sum('jumlah') : 0.0,
            ];
            $this->jumlahkanNode($kegiatanNode);

            $bidangNodes[$bidangKode]['kode'] = $bidangKode;
            $bidangNodes[$bidangKode]['uraian'] = 'BIDANG '.strtoupper($bidangNama);
            $bidangNodes[$bidangKode]['gaya'] = 'bidang';
            $bidangNodes[$bidangKode]['indent'] = 0;
            $bidangNodes[$bidangKode]['children'][$subKode]['kode'] = $subKode;
            $bidangNodes[$bidangKode]['children'][$subKode]['uraian'] = $subNama;
            $bidangNodes[$bidangKode]['children'][$subKode]['gaya'] = 'sub_bidang';
            $bidangNodes[$bidangKode]['children'][$subKode]['indent'] = 1;
            $bidangNodes[$bidangKode]['children'][$subKode]['children'][] = $kegiatanNode;
        }

        uksort($bidangNodes, fn ($a, $b) => strnatcmp((string) $a, (string) $b));

        $belanjaAnggaran = 0.0;
        $belanjaRealisasi = 0.0;
        foreach ($bidangNodes as &$bidangNode) {
            uksort($bidangNode['children'], fn ($a, $b) => strnatcmp((string) $a, (string) $b));
            $bidangNode['children'] = array_values($bidangNode['children']);
            foreach ($bidangNode['children'] as &$subNode) {
                usort($subNode['children'], fn ($a, $b) => strnatcmp((string) $a['kode'], (string) $b['kode']));
            }
            unset($subNode);
            $this->jumlahkanNode($bidangNode);
            $belanjaAnggaran += $bidangNode['anggaran'];
            $belanjaRealisasi += $bidangNode['realisasi'];
        }
        unset($bidangNode);

        $bidangNodes = $this->buangNodeKosong(array_values($bidangNodes));
        $rows = $this->ratakanNode($bidangNodes);

        $tanpaKegiatan = $pengeluarans->whereNull('id_kegiatan');
        if ($tanpaKegiatan->isNotEmpty()) {
            $realisasi = (float) $tanpaKegiatan->sum('jumlah');
            $rows[] = $this->makeRow('5.9.', 'Belanja Lainnya', 'kelompok_belanja', 3, 0.0, $realisasi);
            $belanjaRealisasi += $realisasi;
        }

        return [$rows, $belanjaAnggaran, $belanjaRealisasi];
    }

    /**
     * Nilai anggaran dan realisasi pada level obyek (daun) untuk satu kegiatan.
     *
     * @return array<string, array{anggaran: float, realisasi: float, uraian: string}>
     */
    private function nilaiDaunBelanja($kegiatan, Collection $pengeluarans, Collection $masterRekening): array
    {
        $leaves = [];
        $detailById = [];

        foreach ($kegiatan->belanjaDetails as $detail) {
            $detailById[$detail->id] = $detail;
            $rekening = $detail->rekening
                ?: ($detail->kode_rekening ? $masterRekening->firstWhere('kode', rtrim((string) $detail->kode_rekening, '.')) : null);
            $kode = rtrim((string) ($rekening?->kode ?: $detail->kode_rekening ?: ''), '.');

            if ($kode === '') {
                continue;
            }

            if (! isset($leaves[$kode])) {
                $leaves[$kode] = [
                    'anggaran' => 0.0,
                    'realisasi' => 0.0,
                    'uraian' => (string) ($rekening?->nama ?: $detail->uraian),
                ];
            }

            $leaves[$kode]['anggaran'] += (float) $detail->anggaran;
        }

        foreach ($pengeluarans as $pengeluaran) {
            $terikat = false;

            if ($pengeluaran->kegiatan_belanja_id && isset($detailById[$pengeluaran->kegiatan_belanja_id])) {
                $detail = $detailById[$pengeluaran->kegiatan_belanja_id];
                $rekening = $detail->rekening
                    ?: ($detail->kode_rekening ? $masterRekening->firstWhere('kode', rtrim((string) $detail->kode_rekening, '.')) : null);
                $kode = rtrim((string) ($rekening?->kode ?: $detail->kode_rekening ?: ''), '.');

                if ($kode !== '' && isset($leaves[$kode])) {
                    $leaves[$kode]['realisasi'] += (float) $pengeluaran->jumlah;
                    $terikat = true;
                }
            }

            if (! $terikat && $pengeluaran->rekening_anggaran_id) {
                $rekening = $masterRekening->first(fn ($item) => $item->id === $pengeluaran->rekening_anggaran_id);
                $kode = $rekening?->kode;

                if ($kode && isset($leaves[$kode])) {
                    $leaves[$kode]['realisasi'] += (float) $pengeluaran->jumlah;
                }
            }
        }

        return $leaves;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function lokasiKegiatan($kegiatan, Collection $bidangs, Collection $subBidangs): array
    {
        $subBidang = $kegiatan->subBidang;

        if ($subBidang) {
            return [
                (string) ($subBidang->bidang?->kode ?: '00'),
                (string) ($subBidang->bidang?->nama ?: 'Lainnya'),
                (string) $subBidang->kode,
                (string) $subBidang->nama,
            ];
        }

        $bagian = $kegiatan->kode_rekening
            ? explode('.', rtrim((string) $kegiatan->kode_rekening, '.'))
            : [];
        $bidangKode = $bagian[0] ?? '00';
        $subKode = isset($bagian[1]) ? $bidangKode.'.'.$bagian[1] : $bidangKode.'.00';

        return [
            $bidangKode,
            (string) ($bidangs->firstWhere('kode', $bidangKode)?->nama ?: 'Lainnya'),
            $subKode,
            (string) ($subBidangs->firstWhere('kode', $subKode)?->nama ?: 'Lainnya'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rekeningTree(array $leaves, Collection $masterRekening): array
    {
        $nodes = [];

        foreach ($leaves as $kode => $nilai) {
            $bagian = explode('.', (string) $kode);

            if (count($bagian) < 2 || $bagian[0] !== '5') {
                continue;
            }

            $lintasan = [];
            for ($panjang = 2; $panjang <= count($bagian); $panjang++) {
                $lintasan[] = implode('.', array_slice($bagian, 0, $panjang));
            }

            foreach ($lintasan as $tingkat => $kodeNode) {
                $level = substr_count($kodeNode, '.') + 1;

                if (! isset($nodes[$kodeNode])) {
                    $rekening = $masterRekening->firstWhere('kode', $kodeNode);
                    $nodes[$kodeNode] = [
                        'kode' => $kodeNode,
                        'uraian' => (string) ($rekening?->nama ?: ($kodeNode === $kode ? $nilai['uraian'] : $this->namaRekening($kodeNode))),
                        'gaya' => match ($level) {
                            2 => 'kelompok_belanja',
                            3 => 'jenis_belanja',
                            default => 'obyek',
                        },
                        'indent' => match ($level) {
                            2 => 3,
                            3 => 4,
                            default => 5,
                        },
                        'anggaran' => 0.0,
                        'realisasi' => 0.0,
                        'children' => [],
                    ];
                }

                $nodes[$kodeNode]['anggaran'] += $nilai['anggaran'];
                $nodes[$kodeNode]['realisasi'] += $nilai['realisasi'];

                if ($tingkat > 0) {
                    $induk = $lintasan[$tingkat - 1];
                    if (! in_array($kodeNode, $nodes[$induk]['children'], true)) {
                        $nodes[$induk]['children'][] = $kodeNode;
                    }
                }
            }
        }

        foreach ($nodes as &$node) {
            usort($node['children'], fn ($a, $b) => strnatcmp((string) $a, (string) $b));
        }
        unset($node);

        $akarKode = array_values(array_filter(
            array_keys($nodes),
            fn ($kode) => substr_count((string) $kode, '.') === 1
        ));
        usort($akarKode, fn ($a, $b) => strnatcmp((string) $a, (string) $b));

        return $this->bangunNodeBertingkat($akarKode, $nodes);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bangunNodeBertingkat(array $kodes, array &$nodes): array
    {
        $hasil = [];

        foreach ($kodes as $kode) {
            $node = $nodes[$kode];
            $node['children'] = $this->bangunNodeBertingkat($node['children'], $nodes);

            if ($node['children'] !== []) {
                $node['anggaran'] = array_sum(array_column($node['children'], 'anggaran'));
                $node['realisasi'] = array_sum(array_column($node['children'], 'realisasi'));
            }

            $hasil[] = $node;
        }

        return $hasil;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pendapatanRows(Collection $items, Collection $masterRekening, ?SumberDana $sumberDana): array
    {
        $total = [];

        foreach ($items as $item) {
            $rekening = $item->rekening;
            $kode = (string) ($rekening?->kode ?: $this->kodePendapatanFallback($sumberDana?->kode));
            $anggaran = (float) ($item->anggaran ?? $item->jumlah);
            $realisasi = (float) $item->jumlah;

            foreach ($this->rantaiRekening($kode, $rekening, $masterRekening) as $simpul) {
                if (! isset($total[$simpul['kode']])) {
                    $total[$simpul['kode']] = ['anggaran' => 0.0, 'realisasi' => 0.0, 'nama' => $simpul['nama']];
                }
                $total[$simpul['kode']]['anggaran'] += $anggaran;
                $total[$simpul['kode']]['realisasi'] += $realisasi;
            }
        }

        if ($total === []) {
            $kode = $this->kodePendapatanFallback($sumberDana?->kode);
            $total[$kode] = [
                'anggaran' => 0.0,
                'realisasi' => 0.0,
                'nama' => $this->namaRekening($kode),
            ];
            $induk = explode('.', $kode);
            if (count($induk) >= 2) {
                $kodeInduk = implode('.', array_slice($induk, 0, 2));
                $total[$kodeInduk] = [
                    'anggaran' => 0.0,
                    'realisasi' => 0.0,
                    'nama' => $this->namaRekening($kodeInduk),
                ];
            }
        }

        $anakDari = function (string $induk) use ($masterRekening) {
            return $masterRekening
                ->filter(function ($rekening) use ($induk): bool {
                    $kode = (string) $rekening->kode;

                    return str_starts_with($kode, $induk.'.')
                        && substr_count($kode, '.') === substr_count($induk, '.') + 1;
                })
                ->sortBy('urutan')
                ->values();
        };

        $rows = [];
        $telusuri = function (string $kode, int $indent, string $gaya) use (&$telusuri, $anakDari, $total, &$rows): void {
            if (! isset($total[$kode])) {
                return;
            }
            if ((float) $total[$kode]['anggaran'] === 0.0 && (float) $total[$kode]['realisasi'] === 0.0) {
                return;
            }

            $rows[] = $this->makeRow(
                $this->tampilKode($kode),
                (string) $total[$kode]['nama'],
                $gaya,
                $indent,
                (float) $total[$kode]['anggaran'],
                (float) $total[$kode]['realisasi']
            );

            foreach ($anakDari($kode) as $anak) {
                $telusuri((string) $anak->kode, $indent + 1, 'jenis');
            }
        };

        foreach ($anakDari('4') as $anak) {
            $telusuri((string) $anak->kode, 1, 'kelompok');
        }

        if ($rows === []) {
            foreach ($total as $kode => $nilai) {
                if ($kode === '4') {
                    continue;
                }
                $rows[] = $this->makeRow(
                    $this->tampilKode($kode),
                    (string) $nilai['nama'],
                    substr_count($kode, '.') >= 2 ? 'jenis' : 'kelompok',
                    substr_count($kode, '.'),
                    0.0,
                    0.0
                );
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rantaiRekening(string $kode, ?RekeningAnggaran $rekening, Collection $masterRekening): array
    {
        $rantai = [];

        if ($rekening) {
            $saatIni = $rekening;
            while ($saatIni) {
                $rantai[] = ['kode' => (string) $saatIni->kode, 'nama' => (string) $saatIni->nama];
                $saatIni = $masterRekening->first(fn ($item) => $item->id === $saatIni->parent_id);
            }

            return array_reverse($rantai);
        }

        $bagian = explode('.', rtrim($kode, '.'));
        $kodeSaatIni = '';
        foreach ($bagian as $index => $bagianKode) {
            $kodeSaatIni = $index === 0 ? $bagianKode : $kodeSaatIni.'.'.$bagianKode;
            $rekening = $masterRekening->firstWhere('kode', $kodeSaatIni);
            $rantai[] = [
                'kode' => $kodeSaatIni,
                'nama' => (string) ($rekening?->nama ?: $this->namaRekening($kodeSaatIni)),
            ];
        }

        return $rantai;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pembiayaanRows(Collection $items, string $kodeKelompok, string $namaKelompok, string $gaya, int $indent): array
    {
        $total = ['anggaran' => 0.0, 'realisasi' => 0.0];
        $anak = [];

        foreach ($items as $item) {
            $kode = (string) ($item->rekening?->kode ?: '');
            $nama = (string) ($item->uraian ?: ($item->rekening?->nama ?: $this->namaRekening($kode)));
            $anggaran = (float) $item->anggaran;
            $realisasi = (float) $item->realisasi;

            $total['anggaran'] += $anggaran;
            $total['realisasi'] += $realisasi;

            if ($kode === '') {
                $kode = $kodeKelompok.'.99';
            }

            if (! isset($anak[$kode])) {
                $anak[$kode] = ['anggaran' => 0.0, 'realisasi' => 0.0, 'nama' => $nama];
            }
            $anak[$kode]['anggaran'] += $anggaran;
            $anak[$kode]['realisasi'] += $realisasi;
        }

        uksort($anak, fn ($a, $b) => strnatcmp((string) $a, (string) $b));

        $rows = [$this->makeRow(
            $this->tampilKode($kodeKelompok),
            $namaKelompok,
            $gaya,
            $indent,
            $total['anggaran'],
            $total['realisasi']
        )];

        foreach ($anak as $kode => $nilai) {
            $rows[] = $this->makeRow(
                $this->tampilKode((string) $kode),
                (string) $nilai['nama'],
                'jenis',
                $indent + 1,
                (float) $nilai['anggaran'],
                (float) $nilai['realisasi']
            );
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function jumlahkanNode(array &$node): void
    {
        if ($node['children'] !== []) {
            $node['anggaran'] = 0.0;
            $node['realisasi'] = 0.0;

            foreach ($node['children'] as &$anak) {
                $this->jumlahkanNode($anak);
                $node['anggaran'] += $anak['anggaran'];
                $node['realisasi'] += $anak['realisasi'];
            }
            unset($anak);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function buangNodeKosong(array $nodes): array
    {
        $hasil = [];

        foreach ($nodes as $node) {
            $anak = $this->buangNodeKosong($node['children']);
            $anggaran = $anak === [] ? (float) $node['anggaran'] : array_sum(array_column($anak, 'anggaran'));
            $realisasi = $anak === [] ? (float) $node['realisasi'] : array_sum(array_column($anak, 'realisasi'));

            if ($anggaran === 0.0 && $realisasi === 0.0) {
                continue;
            }

            $node['children'] = $anak;
            $node['anggaran'] = $anggaran;
            $node['realisasi'] = $realisasi;
            $hasil[] = $node;
        }

        return $hasil;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function ratakanNode(array $nodes): array
    {
        $rows = [];

        foreach ($nodes as $node) {
            $denganTitik = ! in_array($node['gaya'], ['bidang', 'sub_bidang'], true);
            $rows[] = $this->makeRow(
                $this->tampilKode((string) $node['kode'], $denganTitik),
                (string) $node['uraian'],
                (string) $node['gaya'],
                (int) $node['indent'],
                (float) $node['anggaran'],
                (float) $node['realisasi']
            );
            $rows = array_merge($rows, $this->ratakanNode($node['children']));
        }

        return $rows;
    }

    private function headerRow(string $kode, string $uraian): array
    {
        return $this->makeRow($kode, $uraian, 'akun', 0, null, null);
    }

    private function totalRow(string $uraian, float $anggaran, float $realisasi): array
    {
        return $this->makeRow('', $uraian, 'total', 0, $anggaran, $realisasi);
    }

    /**
     * @return array{kode: string, uraian: string, gaya: string, indent: int, anggaran: float|null, realisasi: float|null, lebih: float|null}
     */
    private function makeRow(string $kode, string $uraian, string $gaya, int $indent, ?float $anggaran, ?float $realisasi): array
    {
        return [
            'kode' => $kode,
            'uraian' => $uraian,
            'gaya' => $gaya,
            'indent' => $indent,
            'anggaran' => $anggaran,
            'realisasi' => $realisasi,
            'lebih' => $anggaran !== null && $realisasi !== null ? $anggaran - $realisasi : null,
        ];
    }

    private function tampilKode(string $kode, bool $denganTitik = true): string
    {
        $kode = rtrim(trim($kode), '.');

        if ($kode === '') {
            return '';
        }

        return $denganTitik ? $kode.'.' : $kode;
    }

    private function kodePendapatanFallback(?string $kodeSumberDana): string
    {
        return match ($kodeSumberDana) {
            SumberDana::KODE_ALOKASI_DANA_DESA => '4.2.2',
            SumberDana::KODE_DANA_DESA => '4.2.1',
            default => '4.2.1',
        };
    }

    private function namaRekening(string $kode): string
    {
        return self::NAMA_REKENING[rtrim($kode, '.')] ?? 'Rekening '.$kode;
    }
}
