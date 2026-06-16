<div>
    <x-layout.page-header title="Dashboard Bendahara" subtitle="Ringkasan posisi keuangan dan aktivitas desa terkini">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:navigate href="{{ route('bendahara.pemasukan') }}">
                Pemasukan Baru
            </x-ui.button>
            <x-ui.button variant="danger" icon="fas fa-minus" wire:navigate href="{{ route('bendahara.pengeluaran') }}">
                Catat Pengeluaran
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Saldo Kas -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1 text-uppercase">Total Saldo Kas</p>
                        <h3 class="mb-0 fw-bold {{ $saldoKas >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $this->formatRupiah($saldoKas) }}
                        </h3>
                    </div>
                    <div class="stat-icon bg-primary text-white bg-opacity-75">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pemasukan -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--accent-color: var(--success-color);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1 text-uppercase">Total Pemasukan</p>
                        <h3 class="mb-0 fw-bold text-success">{{ $this->formatRupiah($totalPemasukan) }}</h3>
                    </div>
                    <div class="stat-icon bg-success text-white bg-opacity-75">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--accent-color: var(--danger-color);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1 text-uppercase">Total Pengeluaran</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ $this->formatRupiah($totalPengeluaran) }}</h3>
                    </div>
                    <div class="stat-icon bg-danger text-white bg-opacity-75">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kegiatan Aktif -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="--accent-color: var(--info-color);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-semibold mb-1 text-uppercase">Kegiatan Aktif</p>
                        <h3 class="mb-0 fw-bold text-info">{{ $kegiatanAktif }} Program</h3>
                    </div>
                    <div class="stat-icon bg-info text-white bg-opacity-75">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row g-4">
        <!-- Recent Transactions -->
        <div class="col-12 col-xl-8">
            <x-layout.modern-card class="h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-semibold text-body">Transaksi Terkini</h5>
                    <x-ui.button href="{{ route('bendahara.pengeluaran') }}" wire:navigate>Lihat Semua</x-ui.button>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern table-borderless align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>Tipe</th>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $trx)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 32px; height: 32px;">
                                            <i class="{{ $trx['icon'] }}"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ \Carbon\Carbon::parse($trx['tanggal'])->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <x-ui.badge variant="{{ $trx['tipe'] == 'pemasukan' ? 'success' : 'danger' }}">
                                            {{ $trx['kategori'] }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="text-muted small" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $trx['keterangan'] ?? '-' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $trx['tipe'] == 'pemasukan' ? 'text-success' : 'text-danger' }}">
                                        {{ $trx['tipe'] == 'pemasukan' ? '+' : '-' }} {{ $this->formatRupiah($trx['jumlah']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-3 text-light"></i>
                                        <p class="mb-0">Belum ada transaksi terkini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-layout.modern-card>
        </div>

        <!-- Quick Info Widget -->
        <div class="col-12 col-xl-4">
            <x-layout.modern-card class="h-100 bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border: none;">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h5 class="mb-0 fw-semibold text-white">Ringkasan Kegiatan</h5>
                    <i class="fas fa-chart-pie opacity-50 fa-2x"></i>
                </div>

                <div class="mb-4">
                    <p class="mb-1 opacity-75 small text-uppercase">Total Anggaran Disiapkan</p>
                    <h3 class="fw-bold mb-0 text-white">{{ $this->formatRupiah($totalAnggaranKegiatan) }}</h3>
                </div>

                <div class="mt-auto">
                    <p class="mb-3 opacity-75 small">Pastikan pencatatan pengeluaran kegiatan dilakukan secara rutin dan real-time untuk mempermudah laporan ke Kepala Desa.</p>
                    <a href="{{ route('bendahara.kegiatan') }}" class="btn btn-light w-100 text-primary fw-bold border-0 shadow-sm" wire:navigate>
                        Kelola Kegiatan <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </x-layout.modern-card>
        </div>
    </div>
</div>
