<?php

namespace App\Livewire\Admin;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Penggunaan Dana Desa')]
class PenggunaanDanaDesa extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        $pengeluarans = Pengeluaran::with('kegiatan')->orderBy('tanggal', 'desc')->get();

        $totalPengeluaran = $pengeluarans->sum('jumlah');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-penggunaan-dana-desa', [
            'tahun' => date('Y'),
            'tanggalCetak' => \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'pengeluarans' => $pengeluarans,
            'totalPengeluaran' => $totalPengeluaran,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Penggunaan_Dana_Desa_' . date('Ymd_His') . '.pdf');
    }

    public function render()
    {
        // 1. Kas Desa (Total Pemasukan - Total Pengeluaran)
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Data Pengeluaran
        $pengeluarans = Pengeluaran::with('kegiatan')->orderBy('tanggal', 'desc')->get();

        return view('livewire.admin.penggunaan-dana-desa', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'pengeluarans' => $pengeluarans,
        ]);
    }
}
