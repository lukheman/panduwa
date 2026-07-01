<div>
    <x-layout.page-header title="Dashboard Eksekutif" subtitle="Ringkasan Kinerja & Keuangan Pemerintahan Desa">
        <x-slot:actions>
            <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>{{ date('d F Y') }}</span>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Highlight Cards: Keuangan --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $this->formatRupiah($saldoKas) }}</h4>
                        <p class="text-muted mb-0">Total Saldo Kas Desa</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="--accent-color: var(--success-color)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $this->formatRupiah($totalPemasukan) }}</h4>
                        <p class="text-muted mb-0">Akumulasi Pemasukan</p>
                        <small class="text-success"><i class="fas fa-plus-circle me-1"></i>{{ $this->formatRupiah($pemasukanBulanIni) }} bulan ini</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="--accent-color: var(--danger-color)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $this->formatRupiah($totalPengeluaran) }}</h4>
                        <p class="text-muted mb-0">Akumulasi Pengeluaran</p>
                        <small class="text-danger"><i class="fas fa-minus-circle me-1"></i>{{ $this->formatRupiah($pengeluaranBulanIni) }} bulan ini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Section Kiri: Status Kegiatan --}}
        <div class="col-md-8">
            <x-layout.modern-card class="h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-semibold mb-0"><i class="fas fa-tasks text-primary me-2"></i>Status Program & Kegiatan</h5>
                    <x-ui.button href="{{ route('kepala_desa.penggunaan-dana-desa') }}">Lihat Detail Realisasi</x-ui.button>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <h3 class="fw-bold text-primary mb-1">{{ $totalKegiatan }}</h3>
                            <span class="small text-muted text-uppercase fw-semibold">Total Kegiatan</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <h3 class="fw-bold text-warning mb-1">{{ $kegiatanBerjalan }}</h3>
                            <span class="small text-muted text-uppercase fw-semibold">Sedang Berjalan</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <h3 class="fw-bold text-success mb-1">{{ $kegiatanSelesai }}</h3>
                            <span class="small text-muted text-uppercase fw-semibold">Selesai</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kegiatan Terbaru</th>
                                <th>Anggaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatanTerbaru as $k)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-body">{{ $k->nama_kegiatan }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($k->tanggal_mulai)->format('d M Y') }}</small>
                                    </td>
                                    <td class="fw-medium text-primary">{{ $this->formatRupiah($k->anggaran) }}</td>
                                    <td>
                                        @if($k->status === 'selesai')
                                            <x-ui.badge variant="success">Selesai</x-ui.badge>
                                        @elseif($k->status === 'berjalan')
                                            <x-ui.badge variant="primary">Berjalan</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="secondary">Belum Mulai</x-ui.badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada kegiatan yang didaftarkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-layout.modern-card>
        </div>

        {{-- Section Kanan: Aset Desa --}}
        <div class="col-md-4">
            <x-layout.modern-card>
                <div class="text-center py-4">
                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                        <i class="fas fa-boxes fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-body mb-2">Aset Inventaris Desa</h4>
                    <p class="text-muted mb-4">Ringkasan kekayaan fisik desa</p>

                    <div class="bg-white p-3 rounded-3 shadow-sm mb-3 text-start">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Unit Barang</small>
                        <h3 class="fw-bold text-body mb-0">{{ $totalAset }} <span class="fs-6 fw-normal">Item</span></h3>
                    </div>

                    <div class="bg-white p-3 rounded-3 shadow-sm text-start">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Valuasi Aset</small>
                        <h4 class="fw-bold text-success mb-0">{{ $this->formatRupiah($nilaiAset) }}</h4>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    </div>
</div>
