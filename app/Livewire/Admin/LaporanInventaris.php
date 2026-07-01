<?php

namespace App\Livewire\Admin;

use App\Models\Inventaris;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Inventaris Desa')]
class LaporanInventaris extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function downloadPdf()
    {
        $inventaris = Inventaris::orderBy('tanggal_perolehan', 'desc')->get();
        $totalAset = $inventaris->sum('nilai_aset');
        
        $baik = $inventaris->where('kondisi', 'baik')->count();
        $rusakRingan = $inventaris->where('kondisi', 'rusak ringan')->count();
        $rusakBerat = $inventaris->where('kondisi', 'rusak berat')->count();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-inventaris', [
            'tahun' => date('Y'),
            'tanggalCetak' => \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'inventaris' => $inventaris,
            'totalAset' => $totalAset,
            'baik' => $baik,
            'rusakRingan' => $rusakRingan,
            'rusakBerat' => $rusakBerat,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laporan_Inventaris_Desa_' . date('Ymd_His') . '.pdf');
    }

    public function render()
    {
        $inventaris = Inventaris::orderBy('tanggal_perolehan', 'desc')->get();
        $totalAset = $inventaris->sum('nilai_aset');
        
        $baik = $inventaris->where('kondisi', 'baik')->count();
        $rusakRingan = $inventaris->where('kondisi', 'rusak ringan')->count();
        $rusakBerat = $inventaris->where('kondisi', 'rusak berat')->count();

        return view('livewire.admin.laporan-inventaris', [
            'inventaris' => $inventaris,
            'totalAset' => $totalAset,
            'baik' => $baik,
            'rusakRingan' => $rusakRingan,
            'rusakBerat' => $rusakBerat,
        ]);
    }
}
