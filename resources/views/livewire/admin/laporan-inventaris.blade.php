<div>
    <x-layout.page-header title="Laporan Inventaris" subtitle="Daftar Aset dan Inventaris Desa">
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

    {{-- Ringkasan --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <x-layout.modern-card class="border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Nilai Aset</p>
                        <h4 class="fw-bold text-body mb-0">{{ $this->formatRupiah($totalAset) }}</h4>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-3">
            <x-layout.modern-card class="border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Kondisi Baik</p>
                        <h4 class="fw-bold text-body mb-0">{{ $baik }} Barang</h4>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-3">
            <x-layout.modern-card class="border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Rusak Ringan</p>
                        <h4 class="fw-bold text-body mb-0">{{ $rusakRingan }} Barang</h4>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-3">
            <x-layout.modern-card class="border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Rusak Berat</p>
                        <h4 class="fw-bold text-body mb-0">{{ $rusakBerat }} Barang</h4>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    </div>

    {{-- Detail Inventaris --}}
    <x-layout.modern-card class="mb-4">
        <h5 class="fw-semibold text-body mb-4"><i class="fas fa-boxes text-primary me-2"></i>Daftar Aset Inventaris</h5>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Lokasi</th>
                        <th>Tanggal Perolehan</th>
                        <th>Kondisi</th>
                        <th class="text-end">Nilai Aset (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventaris as $item)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $item->kode_barang }}</span></td>
                            <td class="fw-semibold text-body">{{ $item->nama_barang }}</td>
                            <td>{{ $item->lokasi }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_perolehan)->format('d M Y') }}</td>
                            <td>
                                @if($item->kondisi == 'baik')
                                    <span class="badge bg-success">Baik</span>
                                @elseif($item->kondisi == 'rusak ringan')
                                    <span class="badge bg-warning">Rusak Ringan</span>
                                @else
                                    <span class="badge bg-danger">Rusak Berat</span>
                                @endif
                            </td>
                            <td class="text-end text-primary fw-medium">{{ $this->formatRupiah($item->nilai_aset) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fs-1 text-light mb-3"></i>
                                <p>Belum ada data inventaris desa.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($inventaris->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="5" class="fw-bold text-end">TOTAL NILAI ASET</th>
                        <th class="text-end text-primary fw-bold">{{ $this->formatRupiah($totalAset) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-layout.modern-card>
</div>
