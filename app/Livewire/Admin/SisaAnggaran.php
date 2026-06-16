<?php

namespace App\Livewire\Admin;

use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sisa Anggaran')]
class SisaAnggaran extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        $kegiatans = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($kegiatan) {
                $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                $kegiatan->realisasi = $realisasi;
                $kegiatan->sisa = $kegiatan->anggaran - $realisasi;
                $kegiatan->persentase = $kegiatan->anggaran > 0 ? round(($realisasi / $kegiatan->anggaran) * 100, 1) : 0;
                return $kegiatan;
            });

        $totalAnggaran = $kegiatans->sum('anggaran');
        $totalRealisasi = $kegiatans->sum('realisasi');
        $totalSisa = $totalAnggaran - $totalRealisasi;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-realisasi', [
            'tahun' => date('Y'),
            'tanggalCetak' => \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'kegiatans' => $kegiatans,
            'totalAnggaran' => $totalAnggaran,
            'totalRealisasi' => $totalRealisasi,
            'totalSisa' => $totalSisa,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Sisa_Anggaran_' . date('Ymd_His') . '.pdf');
    }

    public function render()
    {
        // 1. Kas Desa (Total Pemasukan - Total Pengeluaran)
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Anggaran Per Kegiatan
        $kegiatans = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($kegiatan) {
                $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                $kegiatan->realisasi = $realisasi;
                $kegiatan->sisa = $kegiatan->anggaran - $realisasi;
                $kegiatan->persentase = $kegiatan->anggaran > 0 ? round(($realisasi / $kegiatan->anggaran) * 100, 1) : 0;
                return $kegiatan;
            });

        // 3. Ringkasan Kegiatan
        $totalAnggaranKegiatan = $kegiatans->sum('anggaran');
        $totalRealisasiKegiatan = $kegiatans->sum('realisasi');
        $totalSisaKegiatan = $totalAnggaranKegiatan - $totalRealisasiKegiatan;

        return view('livewire.admin.sisa-anggaran', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'kegiatans' => $kegiatans,
            'totalAnggaranKegiatan' => $totalAnggaranKegiatan,
            'totalRealisasiKegiatan' => $totalRealisasiKegiatan,
            'totalSisaKegiatan' => $totalSisaKegiatan,
        ]);
    }
}
