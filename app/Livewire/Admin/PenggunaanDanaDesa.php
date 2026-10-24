<?php

namespace App\Livewire\Admin;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use App\Services\LaporanRealisasiApbdesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Realisasi Anggaran')]
class PenggunaanDanaDesa extends Component
{
    public int $tahun;

    public function mount(): void
    {
        $this->tahun = (int) date('Y');
    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function downloadPdf(LaporanRealisasiApbdesService $service)
    {
        $tahun = $this->tahun;

        $sampaiTanggal = $this->tahun.'-12-31';
        $sumberDanaId = SumberDana::where('kode', 'DDS')->value('id');
        $ringkasan = $service->build($this->tahun, $sampaiTanggal, $sumberDanaId);
        $pdf = Pdf::loadView('pdf.laporan-realisasi-apbdes', [
            'tahun' => $tahun,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
            'ringkasan' => $ringkasan,
            'sampaiTanggal' => $sampaiTanggal,
            'sumberDana' => SumberDana::find($sumberDanaId),
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
