<div>
    <x-layout.page-header title="Manajemen Kegiatan" subtitle="Kelola seluruh program dan kegiatan desa">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Kegiatan
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.toast />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-semibold text-body">Daftar Kegiatan</h5>
        <div style="max-width: 300px; width: 100%;">
            <x-form.input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau lokasi kegiatan..."
                icon="fas fa-search"
                class="mb-0"
            />
        </div>
    </div>

    {{-- Kegiatan Grid --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
        @forelse ($kegiatans as $kegiatan)
            <div class="col" wire:key="kegiatan-{{ $kegiatan->id }}">
                <x-layout.modern-card class="h-100 d-flex flex-column p-0 overflow-hidden position-relative">
                    {{-- Card Image / Header --}}
                    <div class="position-relative" style="height: 180px; background: var(--bg-tertiary);">
                        @if($kegiatan->foto_progres)
                            <img src="{{ asset('storage/' . $kegiatan->foto_progres) }}" alt="Progres" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="d-flex align-items-center justify-content-center w-100 h-100 text-muted">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x mb-2 opacity-50"></i>
                                    <p class="small mb-0 opacity-75">Belum ada foto kegiatan</p>
                                </div>
                            </div>
                        @endif

                        {{-- Status Badge (Absolute) --}}
                        <div class="position-absolute" style="top: 1rem; right: 1rem;">
                            <x-ui.badge :variant="$this->getStatusBadgeVariant($kegiatan->status)" :icon="$this->getStatusIcon($kegiatan->status)">
                                {{ ucfirst($kegiatan->status->getLabel()) }}
                            </x-ui.badge>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-4 flex-grow-1 d-flex flex-column">
                        <h5 class="fw-bold mb-2 text-body">{{ $kegiatan->nama_kegiatan }}</h5>

                        <div class="d-flex align-items-center text-muted small mb-4 pb-3 border-bottom">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <span class="text-truncate">{{ $kegiatan->lokasi }}</span>
                        </div>

                        {{-- Financial Progress --}}
                        @php
                            $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                            $persentase = $kegiatan->anggaran > 0 ? min(100, round(($realisasi / $kegiatan->anggaran) * 100)) : 0;
                            $isOverBudget = $realisasi > $kegiatan->anggaran;
                        @endphp
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-semibold">Anggaran:</small>
                                <small class="text-primary fw-bold">{{ $this->formatRupiah($kegiatan->anggaran) }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-semibold">Realisasi:</small>
                                <small class="{{ $isOverBudget ? 'text-danger fw-bold' : 'text-success fw-bold' }}">{{ $this->formatRupiah($realisasi) }}</small>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-2">
                                <div class="progress progress-modern flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar progress-bar-modern {{ $isOverBudget ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $persentase }}%;" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="{{ $isOverBudget ? 'text-danger fw-bold' : 'text-muted fw-bold' }}">{{ $persentase }}%</small>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                        <x-ui.button variant="outline" size="sm" wire:click="openDetailModal({{ $kegiatan->id }})" icon="fas fa-eye">
                            Detail
                        </x-ui.button>

                        <div class="d-flex gap-1">
                            <x-ui.btn-edit wire:click="openEditModal({{ $kegiatan->id }})" tooltip="Edit Kegiatan" />
                            <x-ui.btn-delete wire:click="confirmDelete({{ $kegiatan->id }})" tooltip="Hapus Kegiatan" />
                        </div>
                    </div>
                </x-layout.modern-card>
            </div>
        @empty
            <div class="col-12">
                <x-layout.modern-card class="text-center py-5">
                    <x-ui.empty-state
                        icon="fas fa-tasks"
                        title="Belum ada kegiatan"
                        description="Tambahkan kegiatan baru untuk mulai mengelola program desa."
                    />
                </x-layout.modern-card>
            </div>
        @endforelse
    </div>

    @if ($kegiatans->hasPages())
        <div class="d-flex justify-content-center">
            {{ $kegiatans->links() }}
        </div>
    @endif

    {{-- Modal Create/Edit --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop style="max-height: 90vh; overflow-y: auto;">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingKegiatanId ? 'Edit Kegiatan' : 'Tambah Kegiatan Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <x-form.input
                        id="nama_kegiatan"
                        label="Nama Kegiatan"
                        wire:model="nama_kegiatan"
                        placeholder="Contoh: Pembangunan Jalan Desa"
                        required="true"
                        error="{{ $errors->first('nama_kegiatan') }}"
                    />

                    <x-form.input
                        id="lokasi"
                        label="Lokasi Pelaksanaan"
                        wire:model="lokasi"
                        placeholder="Contoh: Dusun Mawar RT 01"
                        required="true"
                        error="{{ $errors->first('lokasi') }}"
                    />

                    <div class="mb-3">
                        <label class="form-label">Anggaran (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" wire:model="anggaran" min="0" placeholder="0" required>
                        </div>
                        @error('anggaran') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="status" required>
                            <option value="perencanaan">Perencanaan</option>
                            <option value="berjalan">Berjalan</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Progres (Opsional)</label>
                        <input type="file" class="form-control" wire:model="foto_progres" accept="image/*">
                        @error('foto_progres') <span class="text-danger small">{{ $message }}</span> @enderror

                        <div wire:loading wire:target="foto_progres" class="mt-2 text-primary small">
                            <i class="fas fa-spinner fa-spin me-1"></i> Mengunggah gambar...
                        </div>

                        @if ($foto_progres)
                            <div class="mt-3">
                                <p class="small text-muted mb-1">Preview Gambar Baru:</p>
                                <img src="{{ $foto_progres->temporaryUrl() }}" class="rounded" style="max-width: 100%; height: 120px; object-fit: cover; border: 1px solid var(--border-color);">
                            </div>
                        @elseif ($existing_foto_progres)
                            <div class="mt-3">
                                <p class="small text-muted mb-1">Foto Saat Ini:</p>
                                <img src="{{ asset('storage/' . $existing_foto_progres) }}" class="rounded" style="max-width: 100%; height: 120px; object-fit: cover; border: 1px solid var(--border-color);">
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingKegiatanId ? 'Update Kegiatan' : 'Simpan Kegiatan' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Detail --}}
    @if ($showDetailModal && $detailKegiatan)
        <div class="modal-backdrop-custom" wire:click.self="closeDetailModal">
            <div class="modal-content-custom" wire:click.stop style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        Detail Kegiatan
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeDetailModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div>
                    @if($detailKegiatan->foto_progres)
                        <img src="{{ asset('storage/' . $detailKegiatan->foto_progres) }}" alt="Progres" class="w-100 rounded mb-4 shadow-sm" style="height: 250px; object-fit: cover;">
                    @else
                        <div class="w-100 rounded mb-4 d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-image fa-3x mb-2 opacity-50"></i>
                                <p class="mb-0">Belum ada foto progres.</p>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="fw-bold mb-0">{{ $detailKegiatan->nama_kegiatan }}</h4>
                            <x-ui.badge :variant="$this->getStatusBadgeVariant($detailKegiatan->status)" :icon="$this->getStatusIcon($detailKegiatan->status)">
                                {{ ucfirst($detailKegiatan->status) }}
                            </x-ui.badge>
                        </div>
                        <p class="text-muted"><i class="fas fa-map-marker-alt text-danger me-2"></i>{{ $detailKegiatan->lokasi }}</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-12">
                            <div class="p-3 bg-light rounded h-100">
                                <p class="small text-muted mb-1 text-uppercase fw-semibold">Tanggal Dibuat</p>
                                <span class="fw-semibold"><i class="fas fa-calendar text-primary me-2"></i>{{ $detailKegiatan->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded shadow-sm">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Ringkasan Keuangan</h6>
                        @php
                            $realisasi = $detailKegiatan->pengeluarans_sum_jumlah ?? 0;
                            $sisa = $detailKegiatan->anggaran - $realisasi;
                            $persentase = $detailKegiatan->anggaran > 0 ? min(100, round(($realisasi / $detailKegiatan->anggaran) * 100)) : 0;
                            $isOverBudget = $realisasi > $detailKegiatan->anggaran;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Anggaran:</span>
                            <span class="fw-bold text-primary">{{ $this->formatRupiah($detailKegiatan->anggaran) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Realisasi / Terpakai:</span>
                            <span class="fw-bold {{ $isOverBudget ? 'text-danger' : 'text-success' }}">{{ $this->formatRupiah($realisasi) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pt-2 border-top">
                            <span class="fw-semibold">Sisa Anggaran:</span>
                            <span class="fw-bold {{ $sisa < 0 ? 'text-danger' : 'text-body' }}">{{ $this->formatRupiah($sisa) }}</span>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold">Persentase Penggunaan</small>
                                <small class="fw-bold {{ $isOverBudget ? 'text-danger' : 'text-success' }}">{{ $persentase }}%</small>
                            </div>
                            <div class="progress progress-modern" style="height: 8px;">
                                <div class="progress-bar progress-bar-modern {{ $isOverBudget ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $persentase }}%;" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            @if($isOverBudget)
                                <small class="text-danger d-block mt-2"><i class="fas fa-exclamation-triangle me-1"></i> Pengeluaran melebihi anggaran!</small>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 border rounded shadow-sm mt-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Rincian Pemakaian Dana</h6>
                        @if($detailKegiatan->pengeluarans && $detailKegiatan->pengeluarans->count() > 0)
                            <x-layout.table class="table-sm border-bottom mb-0">
                                <x-slot:head>
                                    <tr class="table-light">
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </x-slot:head>
                                @foreach($detailKegiatan->pengeluarans as $pengeluaran)
                                    <tr>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d M Y') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $pengeluaran->kategori->nama_kategori ?? 'Lainnya' }}</div>
                                            <small class="text-muted">{{ $pengeluaran->keterangan ?? '-' }}</small>
                                        </td>
                                        <td class="text-end fw-bold text-danger">-{{ $this->formatRupiah($pengeluaran->jumlah) }}</td>
                                    </tr>
                                @endforeach
                            </x-layout.table>
                        @else
                            <x-ui.empty-state
                                icon="fas fa-receipt"
                                title="Belum Ada Transaksi"
                                description="Belum ada rincian pemakaian dana untuk kegiatan ini."
                                size="sm"
                            />
                        @endif
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeDetailModal">
                            Tutup
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data kegiatan ini? Foto yang terkait juga akan dihapus permanen."
        on-confirm="deleteKegiatan"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Ya, Hapus
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
