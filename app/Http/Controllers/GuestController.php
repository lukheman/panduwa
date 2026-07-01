<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $totalPengeluaran = \App\Models\Pengeluaran::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = \App\Models\Pengeluaran::with(['inventaris'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        return view('guest.landing-page', compact(
            'kegiatans',
            'totalPemasukan',
            'totalPengeluaran',
            'sisaAnggaran',
            'pengeluarans'
        ));
    }

    public function berita()
    {
        $kegiatans = Kegiatan::orderBy('created_at', 'desc')
            ->get();

        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $totalPengeluaran = \App\Models\Pengeluaran::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = \App\Models\Pengeluaran::with(['inventaris'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('guest.berita-dana-desa', compact(
            'kegiatans',
            'totalPemasukan',
            'totalPengeluaran',
            'sisaAnggaran',
            'pengeluarans'
        ));
    }
}
