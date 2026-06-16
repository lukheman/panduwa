<?php

namespace App\Livewire\Guest;

use App\Models\Kegiatan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Berita Penggunaan Dana Desa - PANDUWA')]
class BeritaDanaDesa extends Component
{
    public function render()
    {
        $kegiatans = Kegiatan::withSum('pengeluarans', 'jumlah')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $totalPengeluaran = \App\Models\Pengeluaran::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = \App\Models\Pengeluaran::with(['kategori', 'inventaris'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('livewire.guest.berita-dana-desa', [
            'kegiatans' => $kegiatans,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'sisaAnggaran' => $sisaAnggaran,
            'pengeluarans' => $pengeluarans,
        ]);
    }
    
    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
