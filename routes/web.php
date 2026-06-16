<?php

use Illuminate\Support\Facades\Route;

// Guest & Auth
use App\Livewire\Guest\LandingPage;
use App\Livewire\Auth\Login;

// Admin Core
use App\Http\Controllers\Admin\LogoutController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\ComponentDocs;

// Admin Keuangan & Manajemen
use App\Livewire\Admin\KategoriTransaksiManagement;
use App\Livewire\Admin\PemasukanManagement;
use App\Livewire\Admin\PengeluaranManagement;
use App\Livewire\Admin\KegiatanManagement;
use App\Livewire\Admin\InventarisManagement;
use App\Livewire\Admin\MutasiAsetManagement;
use App\Livewire\Admin\SisaAnggaran;
use App\Livewire\Admin\LaporanRealisasi;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::get('/', LandingPage::class)->name('home');

// Auth Routes
Route::middleware('guest:admin,bendahara,kepala_desa')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Shared App Routes (Logout for all roles)
Route::middleware('auth:admin,bendahara,kepala_desa')->group(function () {
    Route::post('/logout', [LogoutController::class, '__invoke'])->name('logout');
});

// Admin-only Routes
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/profile', Profile::class)->name('admin.profile');

    // Keuangan & Kegiatan
    Route::get('/kategori-transaksi', KategoriTransaksiManagement::class)->name('admin.kategori');
    Route::get('/pemasukan', PemasukanManagement::class)->name('admin.pemasukan');
    Route::get('/pengeluaran', PengeluaranManagement::class)->name('admin.pengeluaran');
    Route::get('/sisa-anggaran', SisaAnggaran::class)->name('admin.sisa-anggaran');
    Route::get('/kegiatan', KegiatanManagement::class)->name('admin.kegiatan');
    Route::get('/laporan-realisasi', LaporanRealisasi::class)->name('admin.laporan-realisasi');

    // Aset & Inventaris
    Route::get('/inventaris', InventarisManagement::class)->name('admin.inventaris');
    Route::get('/mutasi-aset', MutasiAsetManagement::class)->name('admin.mutasi-aset');

    // Pengaturan & Sistem
    Route::get('/users', UserManagement::class)->name('admin.users');
    Route::get('/components', ComponentDocs::class)->name('admin.components');
});

// Bendahara-only Routes
Route::prefix('bendahara')->middleware('auth:bendahara')->group(function () {
    Route::get('/dashboard', \App\Livewire\Bendahara\Dashboard::class)->name('bendahara.dashboard');
    Route::get('/profile', \App\Livewire\Bendahara\Profile::class)->name('bendahara.profile');

    // Keuangan & Kegiatan
    Route::get('/pemasukan', PemasukanManagement::class)->name('bendahara.pemasukan');
    Route::get('/pengeluaran', PengeluaranManagement::class)->name('bendahara.pengeluaran');
    Route::get('/sisa-anggaran', SisaAnggaran::class)->name('bendahara.sisa-anggaran');
    Route::get('/laporan-realisasi', LaporanRealisasi::class)->name('bendahara.laporan-realisasi');
    Route::get('/kegiatan', KegiatanManagement::class)->name('bendahara.kegiatan');

    // Aset & Inventaris
    Route::get('/inventaris', InventarisManagement::class)->name('bendahara.inventaris');
});

// Kepala Desa-only Routes
Route::prefix('kepala-desa')->middleware('auth:kepala_desa')->group(function () {
    Route::get('/dashboard', \App\Livewire\KepalaDesa\Dashboard::class)->name('kepala_desa.dashboard');
    Route::get('/profile', \App\Livewire\KepalaDesa\Profile::class)->name('kepala_desa.profile');
    
    // Keuangan & Laporan
    Route::get('/sisa-anggaran', SisaAnggaran::class)->name('kepala_desa.sisa-anggaran');
    Route::get('/laporan-realisasi', LaporanRealisasi::class)->name('kepala_desa.laporan-realisasi');
});
