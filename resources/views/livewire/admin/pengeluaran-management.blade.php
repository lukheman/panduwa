<div>
    <x-layout.page-header title="Kelola Pengeluaran" subtitle="Catat dan kelola semua pengeluaran keuangan desa">
        <x-slot:actions>
            <x-ui.button variant="danger" icon="fas fa-minus" wire:click="openCreateModal">
                Catat Pengeluaran
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.toast />

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pengeluaran Bulan Ini</p>
                        <h3 class="fw-bold text-body mb-0">{{ $this->formatRupiah($totalBulanIni) }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-calendar-times text-danger fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pengeluaran Keseluruhan</p>
                        <h3 class="fw-bold text-body mb-0">{{ $this->formatRupiah($totalPengeluaran) }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-money-bill-wave text-warning fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Sisa Anggaran Total</p>
                        <h3 class="fw-bold {{ $sisaAnggaran < 0 ? 'text-danger' : 'text-body' }} mb-0">{{ $this->formatRupiah($sisaAnggaran) }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-piggy-bank text-info fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    </div>

    <x-layout.modern-card>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold text-body">Riwayat Pengeluaran</h5>
            <div style="max-width: 300px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari keterangan..."
                    icon="fas fa-search"
                    class="mb-0"
                />
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah (Rp)</th>
                        <th>Relasi</th>
                        <th>Keterangan</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengeluarans as $pengeluaran)
                        <tr wire:key="pengeluaran-{{ $pengeluaran->id }}">
                            <td class="text-secondary">{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d M Y') }}</td>

                            <td class="text-danger fw-bold">{{ $this->formatRupiah($pengeluaran->jumlah) }}</td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if ($pengeluaran->kegiatan)
                                        <span class="badge bg-info">Kegiatan: {{ $pengeluaran->kegiatan->nama_kegiatan }}</span>
                                    @endif
                                    @if ($pengeluaran->inventaris)
                                        <span class="badge bg-primary">Inventaris: {{ $pengeluaran->inventaris->nama_barang }}</span>
                                    @endif
                                    @if (! $pengeluaran->kegiatan && ! $pengeluaran->inventaris)
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-muted">{{ Str::limit($pengeluaran->keterangan ?? '-', 40) }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-view wire:click="openViewModal({{ $pengeluaran->id }})" tooltip="Detail" />
                                    <x-ui.btn-edit wire:click="openEditModal({{ $pengeluaran->id }})" tooltip="Edit" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $pengeluaran->id }})" tooltip="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-ui.empty-state
                                    icon="fas fa-receipt"
                                    title="Belum ada data pengeluaran"
                                    description="Catat pengeluaran pertama untuk memulai pembukuan."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengeluarans->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $pengeluarans->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingPengeluaranId ? 'Edit Pengeluaran' : 'Catat Pengeluaran Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">

                        <div class="mb-3">
                            <label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="tanggal" required>
                            @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class=" mb-3">
                            <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" class="form-control fs-5" wire:model="jumlah" min="0" placeholder="0" required>
                            </div>
                            @error('jumlah') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>





                    <x-form.input
                        id="keterangan"
                        label="Keterangan / Rincian Tambahan"
                        wire:model="keterangan"
                        placeholder="Detail barang/jasa atau catatan lainnya (opsional)"
                        error="{{ $errors->first('keterangan') }}"
                    />

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Terkait Kegiatan <span class="text-muted">(Opsional)</span></label>
                            <select class="form-control" wire:model.live="id_kegiatan">
                                <option value="">Tidak terkait kegiatan</option>
                                @foreach ($kegiatans as $kegiatan)
                                    <option value="{{ $kegiatan->id }}">{{ $kegiatan->nama_kegiatan }}</option>
                                @endforeach
                            </select>
                            @error('id_kegiatan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="catatSebagaiInventaris" wire:model.live="catatSebagaiInventaris">
                                <label class="form-check-label fw-semibold" for="catatSebagaiInventaris">Catat sebagai inventaris</label>
                                <div class="small text-muted">Aktifkan jika pembelian menjadi aset desa.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small mt-3 mb-0">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-info-circle text-primary mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">Petunjuk pencatatan</strong>
                                <ul class="mb-0 ps-3">
    <li>Aktifkan <b>Catat sebagai Inventaris</b> apabila pengeluaran menghasilkan barang yang menjadi aset desa, seperti bangku, laptop, atau printer.</li>
<li>Pilih <b>kegiatan</b> apabila pengeluaran terkait dengan kegiatan atau anggaran tertentu. Pilihan ini bersifat opsional.</li>

                                </ul>
                            </div>
                        </div>
                    </div>

                    @if ($catatSebagaiInventaris)
                        <div class="border rounded-3 bg-light p-3 mt-3">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fas fa-boxes text-primary mt-1"></i>
                                <div>
                                    <h6 class="mb-1">Detail Inventaris Baru</h6>
                                    <p class="small text-muted mb-0">Data aset akan dibuat otomatis bersama pencatatan pengeluaran.</p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="kode_barang" placeholder="Contoh: INV-20260825-AB12">
                                    @error('kode_barang') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="nama_barang" placeholder="Contoh: Bangku kayu">
                                    @error('nama_barang') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kondisi Awal <span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model="kondisi">
                                        @foreach ($kondisiInventaris as $status)
                                            <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                    @error('kondisi') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Tanggal perolehan otomatis mengikuti tanggal transaksi. Nilai aset otomatis mengikuti jumlah pengeluaran.
                            </div>
                        </div>
                    @endif
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="danger">
                            {{ $editingPengeluaranId ? 'Perbarui' : 'Simpan Pengeluaran' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showViewModal && $viewingPengeluaran)
        <div class="modal-backdrop-custom" wire:click.self="closeViewModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">Detail Pengeluaran</h5>
                    <button type="button" class="modal-close-btn" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-3">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Tanggal Transaksi</td>
                            <td>: <span class="fw-semibold">{{ \Carbon\Carbon::parse($viewingPengeluaran->tanggal)->format('d F Y') }}</span></td>
                        </tr>

                        <tr>
                            <td class="text-muted">Jumlah</td>
                            <td>: <span class="fw-bold text-danger">{{ $this->formatRupiah($viewingPengeluaran->jumlah) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terkait Kegiatan</td>
                            <td>:
                                @if($viewingPengeluaran->kegiatan)
                                    <x-ui.badge variant="info" icon="fas fa-tasks">{{ $viewingPengeluaran->kegiatan->nama_kegiatan }}</x-ui.badge>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terkait Inventaris</td>
                            <td>:
                                @if ($viewingPengeluaran->inventaris)
                                    <x-ui.badge variant="primary" icon="fas fa-boxes">{{ $viewingPengeluaran->inventaris->nama_barang }}</x-ui.badge>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>: {{ $viewingPengeluaran->keterangan ?? '-' }}</td>
                        </tr>
                    </table>


                </div>
                <div class="d-flex justify-content-end mt-2">
                    <x-ui.button type="button" variant="secondary" wire:click="closeViewModal">Tutup</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus catatan pengeluaran ini?"
        on-confirm="deletePengeluaran"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Ya, Hapus
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
