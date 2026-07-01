<div>
    <x-layout.page-header title="Sisa Anggaran" subtitle="Laporan Penggunaan Dana Desa Desa & Realisasi Kegiatan">
        <x-slot:actions>
            <x-ui.button variant="danger" wire:click="downloadPdf" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="downloadPdf">
                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                </span>
                <span wire:loading wire:target="downloadPdf">
                    <i class="fas fa-spinner fa-spin me-2"></i> Mengunduh...
                </span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Ringkasan Kas Global --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pemasukan</p>
                        <h3 class="fw-bold text-body mb-0 fs-4">{{ $this->formatRupiah($totalPemasukan) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-arrow-down text-success fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pengeluaran</p>
                        <h3 class="fw-bold text-body mb-0 fs-4">{{ $this->formatRupiah($totalPengeluaran) }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-arrow-up text-danger fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 {{ $saldoKas >= 0 ? 'border-primary' : 'border-danger' }} h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Sisa Saldo Kas Desa</p>
                        <h3 class="fw-bold text-body mb-0 fs-4">{{ $this->formatRupiah($saldoKas) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-wallet text-primary fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    </div>

    {{-- Detail Pengeluaran --}}
    <x-layout.modern-card class="mb-4">
        <h5 class="fw-semibold text-body mb-4"><i class="fas fa-list text-primary me-2"></i>Daftar Pengeluaran Dana Desa</h5>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Terkait Kegiatan</th>
                        <th class="text-end">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengeluarans as $pengeluaran)
                        <tr>
                            <td>
                                <div class="fw-semibold text-body">{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d M Y') }}</div>
                            </td>
                            <td>
                                {{ $pengeluaran->keterangan ?: '-' }}
                            </td>
                            <td>
                                @if($pengeluaran->kegiatan)
                                    <span class="badge bg-primary badge-modern">{{ $pengeluaran->kegiatan->nama_kegiatan }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-medium">
                                {{ $this->formatRupiah($pengeluaran->jumlah) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fs-1 text-light mb-3"></i>
                                <p>Belum ada data pengeluaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($pengeluarans->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="3" class="fw-bold text-end">TOTAL PENGELUARAN</th>
                        <th class="text-end text-danger fw-bold">{{ $this->formatRupiah($totalPengeluaran) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-layout.modern-card>
</div>
