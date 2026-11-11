<?php

namespace App\Livewire\Admin;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use App\Services\LaporanRealisasiApbdesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Laporan Realisasi Anggaran')]
class PenggunaanDanaDesa extends Component
{
    public int $tahun;

    #[Url(as: 'dana')]
    public string $dana = SumberDana::KODE_DANA_DESA;

    public function mount(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
        $this->tahun = (int) date('Y');
    }

    public function updatedDana(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function downloadPdf(LaporanRealisasiApbdesService $service)
    {
        $tahun = $this->tahun;

        $sampaiTanggal = $this->tahun.'-12-31';
        $sumberDana = SumberDana::where('kode', $this->dana)->first();
        $sumberDanaId = $sumberDana?->id;
        $ringkasan = $service->build($this->tahun, $sampaiTanggal, $sumberDanaId);
        $pdf = Pdf::loadView('pdf.laporan-realisasi-apbdes', [
            'tahun' => $tahun,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
            'ringkasan' => $ringkasan,
            'sampaiTanggal' => $sampaiTanggal,
            'sumberDana' => $sumberDana,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Realisasi_'.str_replace(' ', '_', SumberDana::labelUntukKode($this->dana)).'_'.date('Ymd_His').'.pdf');
    }

    public function render()
    {
        $sumberDana = SumberDana::where('kode', $this->dana)->first();
        $sumberDanaId = $sumberDana?->id;

        $totalPemasukan = Pemasukan::query()->untukSumberDana($sumberDanaId)->sum('jumlah');
        $totalPengeluaran = Pengeluaran::query()->untukSumberDana($sumberDanaId)->sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = Pengeluaran::query()
            ->untukSumberDana($sumberDanaId)
            ->with(['kegiatan.subBidang.bidang', 'inventaris.sumberDana'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('livewire.admin.penggunaan-dana-desa', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'pengeluarans' => $pengeluarans,
            'sumberDana' => $sumberDana,
            'danaLabel' => SumberDana::labelUntukKode($this->dana),
        ]);
    }
}
