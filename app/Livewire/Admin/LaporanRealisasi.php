<?php

namespace App\Livewire\Admin;

use App\Models\Kegiatan;
use Livewire\Attributes\Title;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

#[Title('Laporan Realisasi')]
class LaporanRealisasi extends Component
{
    public $tahun;

    public function mount()
    {
        $this->tahun = date('Y');
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        // Get all activities that have some start/end date in the given year, 
        // or just all activities created in that year, or simply all activities.
        // Let's filter by year of created_at or just get all and filter in memory if needed.
        $kegiatans = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->whereYear('created_at', $this->tahun)
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

        $pdf = Pdf::loadView('pdf.laporan-realisasi', [
            'kegiatans' => $kegiatans,
            'tahun' => $this->tahun,
            'totalAnggaran' => $totalAnggaran,
            'totalRealisasi' => $totalRealisasi,
            'totalSisa' => $totalSisa,
            'tanggalCetak' => date('d F Y')
        ]);
        
        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Realisasi_Dana_Desa_' . $this->tahun . '.pdf');
    }

    public function render()
    {
        $kegiatans = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->whereYear('created_at', $this->tahun)
            ->get()
            ->map(function ($kegiatan) {
                $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                $kegiatan->realisasi = $realisasi;
                $kegiatan->sisa = $kegiatan->anggaran - $realisasi;
                $kegiatan->persentase = $kegiatan->anggaran > 0 ? round(($realisasi / $kegiatan->anggaran) * 100, 1) : 0;
                return $kegiatan;
            });

        // Get available years for the dropdown
        $availableYears = Kegiatan::selectRaw('YEAR(created_at) as year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year')
                            ->toArray();
        
        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, date('Y'));
        }

        return view('livewire.admin.laporan-realisasi', [
            'kegiatans' => $kegiatans,
            'availableYears' => $availableYears
        ]);
    }
}
