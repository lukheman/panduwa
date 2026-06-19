<?php

namespace App\Livewire\KepalaDesa;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\Kegiatan;
use App\Models\Inventaris;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Kepala Desa')]
class Dashboard extends Component
{
    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function render()
    {
        // 1. Ringkasan Kas
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        // 2. Statistik Kegiatan
        $totalKegiatan = Kegiatan::count();
        $kegiatanBerjalan = Kegiatan::where('status', \App\Enums\StatusKegiatan::BERJALAN)->count();
        $kegiatanSelesai = Kegiatan::where('status', \App\Enums\StatusKegiatan::SELESAI)->count();
        $kegiatanBelumMulai = Kegiatan::where('status', \App\Enums\StatusKegiatan::PERENCANAAN)->count();

        // 3. Ringkasan Aset
        $totalAset = Inventaris::count();
        $nilaiAset = Inventaris::sum('nilai_aset');

        // 4. Kegiatan Terbaru
        $kegiatanTerbaru = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Pemasukan & Pengeluaran Bulan Ini
        $pemasukanBulanIni = Pemasukan::whereMonth('tanggal', date('m'))
                                      ->whereYear('tanggal', date('Y'))
                                      ->sum('jumlah');
        $pengeluaranBulanIni = Pengeluaran::whereMonth('tanggal', date('m'))
                                          ->whereYear('tanggal', date('Y'))
                                          ->sum('jumlah');

        return view('livewire.kepala-desa.dashboard', [
            'saldoKas' => $saldoKas,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'pemasukanBulanIni' => $pemasukanBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'totalKegiatan' => $totalKegiatan,
            'kegiatanBerjalan' => $kegiatanBerjalan,
            'kegiatanSelesai' => $kegiatanSelesai,
            'kegiatanBelumMulai' => $kegiatanBelumMulai,
            'totalAset' => $totalAset,
            'nilaiAset' => $nilaiAset,
            'kegiatanTerbaru' => $kegiatanTerbaru,
        ]);
    }
}
