<?php

namespace App\Livewire\Bendahara;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\Kegiatan;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Bendahara')]
class Dashboard extends Component
{
    public function render()
    {
        // 1. Kalkulasi Statistik Keuangan
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Statistik Kegiatan
        $kegiatanAktif = Kegiatan::whereIn('status', [\App\Enums\StatusKegiatan::PERENCANAAN, \App\Enums\StatusKegiatan::BERJALAN])->count();
        $totalAnggaranKegiatan = Kegiatan::sum('anggaran');
        
        // 3. Aktivitas Keuangan Terbaru (Gabungan 5 Pemasukan & Pengeluaran Terakhir)
        $recentPemasukan = Pemasukan::query()
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'masuk_'.$item->id,
                    'tipe' => 'pemasukan',
                    'tanggal' => $item->tanggal,
                    'jumlah' => $item->jumlah,
                    'kategori' => $item->sumber_dana,
                    'keterangan' => $item->keterangan,
                    'icon' => 'fas fa-arrow-down text-success',
                ];
            });

        $recentPengeluaran = Pengeluaran::with('kategori')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'keluar_'.$item->id,
                    'tipe' => 'pengeluaran',
                    'tanggal' => $item->tanggal,
                    'jumlah' => $item->jumlah,
                    'kategori' => $item->kategori->nama_kategori ?? 'Pengeluaran',
                    'keterangan' => $item->keterangan,
                    'icon' => 'fas fa-arrow-up text-danger',
                ];
            });

        $recentTransactions = $recentPemasukan->concat($recentPengeluaran)
            ->sortByDesc('tanggal')
            ->take(5);

        return view('livewire.bendahara.dashboard', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'kegiatanAktif' => $kegiatanAktif,
            'totalAnggaranKegiatan' => $totalAnggaranKegiatan,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
