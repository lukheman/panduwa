<div>
    <x-layout.page-header title="Laporan Realisasi Dana Desa" subtitle="Cetak Laporan Realisasi Penggunaan Anggaran Kegiatan">
        <x-slot:actions>
            <x-ui.button wire:click="downloadPdf" variant="primary" icon="fas fa-file-pdf">
                Unduh PDF Laporan
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-layout.modern-card class="mb-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="fw-semibold text-body mb-0"><i class="fas fa-filter text-primary me-2"></i>Filter Laporan</h5>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex align-items-center">
                    <label class="fw-semibold me-3 mb-0">Tahun Anggaran:</label>
                    <select wire:model.live="tahun" class="form-select w-auto d-inline-block">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kegiatan</th>
                        <th>Anggaran (Rp)</th>
                        <th>Realisasi (Rp)</th>
                        <th>Sisa (Rp)</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kegiatans as $index => $kegiatan)
                        <tr>
                            <td class="text-secondary fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold text-body">{{ $kegiatan->nama_kegiatan }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('d M Y') }}</small>
                            </td>
                            <td class="text-primary fw-medium">{{ $this->formatRupiah($kegiatan->anggaran) }}</td>
                            <td class="text-danger fw-medium">{{ $this->formatRupiah($kegiatan->realisasi) }}</td>
                            <td class="text-success fw-bold">{{ $this->formatRupiah($kegiatan->sisa) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress progress-modern flex-grow-1">
                                        <div class="progress-bar progress-bar-modern {{ $kegiatan->persentase >= 100 ? 'bg-danger' : ($kegiatan->persentase >= 75 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ min($kegiatan->persentase, 100) }}%" 
                                             aria-valuenow="{{ $kegiatan->persentase }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <span class="small fw-semibold" style="min-width: 45px;">{{ $kegiatan->persentase }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-ui.empty-state
                                    icon="fas fa-file-invoice-dollar"
                                    title="Tidak ada kegiatan"
                                    description="Belum ada data kegiatan untuk tahun anggaran {{ $tahun }}."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($kegiatans->count() > 0)
                <tfoot class="bg-light border-top border-2">
                    <tr>
                        <th colspan="2" class="text-end fw-bold">TOTAL KESELURUHAN</th>
                        <th class="text-primary fw-bold">{{ $this->formatRupiah($kegiatans->sum('anggaran')) }}</th>
                        <th class="text-danger fw-bold">{{ $this->formatRupiah($kegiatans->sum('realisasi')) }}</th>
                        <th class="text-success fw-bold">{{ $this->formatRupiah($kegiatans->sum('sisa')) }}</th>
                        <th>
                            @php
                                $totAng = $kegiatans->sum('anggaran');
                                $totReal = $kegiatans->sum('realisasi');
                                $totPer = $totAng > 0 ? round(($totReal / $totAng) * 100, 1) : 0;
                            @endphp
                            <span class="fw-bold">{{ $totPer }}%</span>
                        </th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-layout.modern-card>
</div>
