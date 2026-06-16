<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Admin;
use App\Models\Bendahara;
use App\Models\KepalaDesa;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use App\Models\Inventaris;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;

#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render()
    {
        $totalUsers = Admin::count() + Bendahara::count() + KepalaDesa::count();
        $totalKategori = KategoriTransaksi::count();
        $totalKegiatan = Kegiatan::count();
        $totalInventaris = Inventaris::count();
        
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');

        // Recent users (mix from 3 tables, since it's hard to order by created_at across 3 tables efficiently in Eloquent without union, we can just get latest from each and merge, then sort)
        $admins = Admin::latest()->take(3)->get()->map(function($u) {
            $u->role = 'Admin';
            return $u;
        });
        $bendaharas = Bendahara::latest()->take(3)->get()->map(function($u) {
            $u->role = 'Bendahara';
            return $u;
        });
        $kades = KepalaDesa::latest()->take(3)->get()->map(function($u) {
            $u->role = 'Kepala Desa';
            return $u;
        });

        $recentUsers = $admins->concat($bendaharas)->concat($kades)
            ->sortByDesc('created_at')
            ->take(5);

        return view('livewire.admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalKategori' => $totalKategori,
            'totalKegiatan' => $totalKegiatan,
            'totalInventaris' => $totalInventaris,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'recentUsers' => $recentUsers,
        ]);
    }
}
