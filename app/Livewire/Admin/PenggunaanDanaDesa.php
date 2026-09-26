<?php

namespace App\Livewire\Admin;

use App\Models\BidangKegiatan;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Realisasi Anggaran')]
class PenggunaanDanaDesa extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        $tahun = date('Y');

        $totalPemasukan = Pemasukan::sum('jumlah');

        $bidangs = BidangKegiatan::with(['subBidangs.kegiatans'])
            ->orderBy('kode')
            ->get();

        $totalRencana = Kegiatan::sum('rencana_anggaran');
        $totalRealisasi = Kegiatan::sum('realisasi_anggaran');

        $pdf = Pdf::loadView('pdf.laporan-penggunaan-dana-desa', [
            'tahun' => $tahun,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
            'totalPemasukan' => $totalPemasukan,
            'bidangs' => $bidangs,
            'totalRencana' => $totalRencana,
            'totalRealisasi' => $totalRealisasi,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Realisasi_Anggaran_'.date('Ymd_His').'.pdf');
    }

    public function render()
    {
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = Pengeluaran::with(['kegiatan.subBidang.bidang', 'inventaris'])->orderBy('tanggal', 'desc')->get();

        return view('livewire.admin.penggunaan-dana-desa', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'pengeluarans' => $pengeluarans,
        ]);
    }
}
