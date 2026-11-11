<?php

namespace App\Livewire\Admin;

use App\Enums\KondisiInventaris;
use App\Models\Inventaris;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Inventaris Desa')]
class LaporanInventaris extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        [$inventaris, $totalAset, $totalUnit, $baik, $rusakRingan, $rusakBerat] = $this->getLaporanData();

        $pdf = Pdf::loadView('pdf.laporan-inventaris', [
            'tahun' => date('Y'),
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y'),
            'inventaris' => $inventaris,
            'totalAset' => $totalAset,
            'totalUnit' => $totalUnit,
            'baik' => $baik,
            'rusakRingan' => $rusakRingan,
            'rusakBerat' => $rusakBerat,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Inventaris_Desa_'.date('Ymd_His').'.pdf');
    }

    public function render()
    {
        [$inventaris, $totalAset, $totalUnit, $baik, $rusakRingan, $rusakBerat] = $this->getLaporanData();

        return view('livewire.admin.laporan-inventaris', [
            'inventaris' => $inventaris,
            'totalAset' => $totalAset,
            'totalUnit' => $totalUnit,
            'baik' => $baik,
            'rusakRingan' => $rusakRingan,
            'rusakBerat' => $rusakBerat,
        ]);
    }

    /**
     * @return array{0: Collection<int, Inventaris>, 1: float|int, 2: int, 3: int, 4: int, 5: int}
     */
    protected function getLaporanData(): array
    {
        $inventaris = Inventaris::with(['sumberDana', 'pengeluaran.sumberDana'])
            ->orderBy('tanggal_perolehan', 'desc')
            ->get();
        $totalAset = $inventaris->sum('nilai_aset');
        $totalUnit = $inventaris->sum('jumlah');

        $baik = $inventaris->where('kondisi', KondisiInventaris::BAIK)->sum('jumlah');
        $rusakRingan = $inventaris->where('kondisi', KondisiInventaris::RUSAK_RINGAN)->sum('jumlah');
        $rusakBerat = $inventaris->where('kondisi', KondisiInventaris::RUSAK_BERAT)->sum('jumlah');

        return [$inventaris, $totalAset, $totalUnit, $baik, $rusakRingan, $rusakBerat];
    }
}
