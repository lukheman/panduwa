<div>
    <x-layout.page-header title="Sisa Anggaran" subtitle="Laporan Sisa Anggaran Desa & Realisasi Kegiatan">
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

    {{-- Detail Anggaran Kegiatan --}}
    <x-layout.modern-card class="mb-4">
        <h5 class="fw-semibold text-body mb-4"><i class="fas fa-chart-pie text-primary me-2"></i>Realisasi Anggaran per Kegiatan</h5>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Nama Kegiatan</th>
                        <th>Anggaran (Rp)</th>
                        <th>Realisasi (Rp)</th>
                        <th>Sisa (Rp)</th>
                        <th style="width: 150px;">Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kegiatans as $kegiatan)
                        <tr>
                            <td>
                                <div class="fw-semibold text-body">{{ $kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('d M Y') }}</small>
                            </td>
                            <td class="text-primary fw-medium">{{ $this->formatRupiah($kegiatan->anggaran) }}</td>
                            <td class="text-danger fw-medium">{{ $this->formatRupiah($kegiatan->realisasi) }}</td>
                            <td class="text-success fw-bold">{{ $this->formatRupiah($kegiatan->sisa) }}</td>
                            <td>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold">{{ $kegiatan->persentase }}%</span>
                                </div>
                                <div class="progress progress-modern">
                                    <div class="progress-bar progress-bar-modern {{ $kegiatan->persentase >= 100 ? 'bg-danger' : ($kegiatan->persentase >= 75 ? 'bg-warning' : 'bg-success') }}"
                                         role="progressbar"
                                         style="width: {{ min($kegiatan->persentase, 100) }}%"
                                         aria-valuenow="{{ $kegiatan->persentase }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td>
                                @if($kegiatan->status === 'selesai')
                                    <span class="badge bg-success badge-modern"><i class="fas fa-check-circle"></i> Selesai</span>
                                @elseif($kegiatan->status === 'berjalan')
                                    <span class="badge bg-primary badge-modern"><i class="fas fa-spinner fa-spin"></i> Berjalan</span>
                                @else
                                    <span class="badge bg-secondary badge-modern"><i class="fas fa-clock"></i> Belum Mulai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-tasks fs-1 text-light mb-3"></i>
                                <p>Belum ada data kegiatan untuk dianalisis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($kegiatans->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <th class="fw-bold">TOTAL KESELURUHAN</th>
                        <th class="text-primary fw-bold">{{ $this->formatRupiah($totalAnggaranKegiatan) }}</th>
                        <th class="text-danger fw-bold">{{ $this->formatRupiah($totalRealisasiKegiatan) }}</th>
                        <th class="text-success fw-bold">{{ $this->formatRupiah($totalSisaKegiatan) }}</th>
                        <th colspan="2">
                            @php
                                $totalPersentase = $totalAnggaranKegiatan > 0 ? round(($totalRealisasiKegiatan / $totalAnggaranKegiatan) * 100, 1) : 0;
                            @endphp
                            Serapan Dana: {{ $totalPersentase }}%
                        </th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-layout.modern-card>
</div>
