<div>
    <!-- Hero Section for Berita Page -->
    <section class="hero position-relative d-flex align-items-center justify-content-center" style="background: url('{{ asset('assets/images/kantor-desa.png') }}') center/cover no-repeat; margin-top: -73px; padding-top: 140px; padding-bottom: 100px; min-height: 450px;">
        <!-- Dark Overlay for text readability -->
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.7; z-index: 0;"></div>

        <div class="container position-relative z-1 text-white text-center mt-5">
            <span class="badge bg-white text-dark px-3 py-2 rounded-pill mb-4 fw-semibold shadow-sm">
                <i class="fas fa-bullhorn text-danger me-1"></i> Transparansi Publik
            </span>
            <h1 class="display-4 fw-bold mb-3 text-shadow-sm">Berita & Informasi Penggunaan Dana Desa</h1>
            <p class="lead text-light mx-auto text-shadow-sm" style="max-width: 800px;">
                Sebagai bentuk komitmen, berikut adalah ringkasan pengelolaan keuangan, pelaksanaan program kerja, hingga rincian pengeluaran operasional terkini di Desa Waindawula.
            </p>
        </div>

        <!-- Shape Divider -->
        <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="line-height: 0;">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="display: block; width: 100%; height: 60px; fill: #f8f9fa;">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,119.3,198.71,108.68,241.1,101.42,283.47,78.53,321.39,56.44Z"></path>
            </svg>
        </div>
    </section>

    <div class="bg-light py-5">
        <div class="container">
            <!-- Ringkasan Keuangan Desa -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white border-start border-4 border-primary">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-wallet fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase mb-1" style="font-size: 0.8rem;">Total Anggaran / Pemasukan</h6>
                                <h4 class="fw-bold mb-0 text-dark">{{ $this->formatRupiah($totalPemasukan) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white border-start border-4 border-danger">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 me-3">
                                <i class="fas fa-hand-holding-usd fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase mb-1" style="font-size: 0.8rem;">Total Realisasi / Pengeluaran</h6>
                                <h4 class="fw-bold mb-0 text-dark">{{ $this->formatRupiah($totalPengeluaran) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white border-start border-4 border-success">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                <i class="fas fa-piggy-bank fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase mb-1" style="font-size: 0.8rem;">Sisa Kas Desa</h6>
                                <h4 class="fw-bold mb-0 text-dark">{{ $this->formatRupiah($sisaAnggaran) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pelaksanaan Kegiatan -->
            <h4 class="fw-bold mb-4 border-start border-4 border-primary ps-3">Pelaksanaan Kegiatan Desa</h4>
            <div class="row g-4 mb-5">
                @forelse($kegiatans as $kegiatan)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden transition-hover">
                            @if($kegiatan->foto_progres)
                                <img src="{{ Storage::url($kegiatan->foto_progres) }}" class="card-img-top object-fit-cover" alt="{{ $kegiatan->nama_kegiatan }}" style="height: 200px;">
                            @else
                                <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fs-1 text-secondary opacity-50"></i>
                                </div>
                            @endif
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                        <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('d M Y') }}
                                    </span>
                                    <span class="badge {{ $kegiatan->status === 'selesai' ? 'bg-success' : ($kegiatan->status === 'berjalan' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ ucfirst($kegiatan->status->getLabel()) }}
                                    </span>
                                </div>
                                <h5 class="card-title fw-bold mb-3">{{ $kegiatan->nama_kegiatan }}</h5>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Anggaran:</span>
                                        <span class="fw-semibold text-body">{{ $this->formatRupiah($kegiatan->anggaran) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Realisasi Dana:</span>
                                        <span class="fw-semibold text-danger">{{ $this->formatRupiah($kegiatan->pengeluarans_sum_jumlah ?? 0) }}</span>
                                    </div>

                                    @php
                                        $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                                        $persen = $kegiatan->anggaran > 0 ? min(100, round(($realisasi / $kegiatan->anggaran) * 100)) : 0;
                                    @endphp
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persen }}%" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <p class="card-text text-secondary small mb-0">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $kegiatan->lokasi ?? 'Desa Waindawula' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="bg-white p-5 rounded-4 shadow-sm border">
                            <i class="fas fa-newspaper fs-1 text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted fw-semibold">Belum ada berita kegiatan</h5>
                            <p class="text-secondary small mb-0">Informasi pelaksanaan kegiatan dana desa akan tampil di sini.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Riwayat Pengeluaran Terkini -->
            <h4 class="fw-bold mb-4 border-start border-4 border-danger ps-3">Rincian Pengeluaran Keseluruhan</h4>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3">Kategori</th>
                                <th class="py-3">Keterangan / Item</th>
                                <th class="py-3 text-end px-4">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengeluarans as $pengeluaran)
                                <tr>
                                    <td class="px-4 text-muted" style="width: 140px;">
                                        {{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                            {{ $pengeluaran->kategori->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pengeluaran->keterangan ?? 'Pembayaran/Pengeluaran Operasional' }}</div>
                                        @if($pengeluaran->inventaris)
                                            <div class="mt-1">
                                                <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">
                                                    <i class="fas fa-box-open me-1"></i> Aset Inventaris
                                                </span>
                                                <small class="text-muted ms-1">{{ $pengeluaran->inventaris->nama_barang }} ({{ $pengeluaran->inventaris->kode_barang }})</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end px-4 text-danger fw-bold">
                                        {{ $this->formatRupiah($pengeluaran->jumlah) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada catatan pengeluaran terkini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot:styles>
        <style>
            .text-shadow-sm {
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }
            .transition-hover {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .transition-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
            }
        </style>
    </x-slot:styles>
</div>
