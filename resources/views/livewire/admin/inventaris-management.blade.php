<div>
    <x-layout.page-header title="Manajemen Inventaris" subtitle="Buku Induk Aset dan Barang Inventaris Desa">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Inventaris
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))
        <x-ui.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Barang/Aset</p>
                        <h3 class="fw-bold text-body mb-0">{{ $totalAset }} <span class="fs-6 text-muted fw-normal">Item</span></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-boxes text-primary fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Nilai Aset</p>
                        <h3 class="fw-bold text-body mb-0 fs-4">{{ $this->formatRupiah($totalNilaiAset) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-money-check-alt text-success fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Aset Kondisi Baik</p>
                        <h3 class="fw-bold text-body mb-0">{{ $asetKondisiBaik }} <span class="fs-6 text-muted fw-normal">Item</span></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-circle text-info fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    </div>

    <x-layout.modern-card>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold text-body">Daftar Aset & Barang</h5>
            <div style="max-width: 350px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari kode, nama barang, atau lokasi..."
                    icon="fas fa-search"
                    class="mb-0"
                />
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Kode & Barang</th>
                        <th>Lokasi</th>
                        <th>Nilai Aset (Rp)</th>
                        <th>Kondisi</th>
                        <th>Asal Pengeluaran</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventarises as $inventaris)
                        <tr wire:key="inventaris-{{ $inventaris->id }}">
                            <td>
                                <div class="fw-semibold text-body">{{ $inventaris->nama_barang }}</div>
                                <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>{{ $inventaris->kode_barang }}</small>
                            </td>
                            <td class="text-secondary"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $inventaris->lokasi }}</td>
                            <td class="text-success fw-bold">{{ $this->formatRupiah($inventaris->nilai_aset) }}</td>
                            <td>
                                <x-ui.badge :variant="$this->getKondisiBadgeVariant($inventaris->kondisi)">
                                    {{ $inventaris->kondisi }}
                                </x-ui.badge>
                            </td>
                            <td>
                                @if($inventaris->pengeluaran)
                                    <div class="small text-secondary" title="{{ $inventaris->pengeluaran->keterangan }}">
                                        <i class="fas fa-receipt text-danger me-1"></i> Rp {{ number_format($inventaris->pengeluaran->jumlah, 0, ',', '.') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($inventaris->pengeluaran->tanggal)->format('d M Y') }}</small>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-view wire:click="openViewModal({{ $inventaris->id }})" tooltip="Detail" :iconOnly="true" />
                                    <x-ui.btn-edit wire:click="openEditModal({{ $inventaris->id }})" tooltip="Edit" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $inventaris->id }})" tooltip="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-ui.empty-state
                                    icon="fas fa-boxes"
                                    title="Belum ada data inventaris"
                                    description="Tambahkan data aset atau barang pertama desa ke dalam buku induk."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventarises->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $inventarises->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop style="max-height: 90vh; overflow-y: auto; max-width: 600px;">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingInventarisId ? 'Edit Inventaris' : 'Tambah Inventaris Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-form.input
                                id="kode_barang"
                                label="Kode Barang"
                                wire:model="kode_barang"
                                placeholder="Contoh: INV-001"
                                required="true"
                                error="{{ $errors->first('kode_barang') }}"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Perolehan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="tanggal_perolehan" required>
                            @error('tanggal_perolehan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <x-form.input
                        id="nama_barang"
                        label="Nama Barang"
                        wire:model="nama_barang"
                        placeholder="Contoh: Laptop Asus VivoBook, Kursi Rapat"
                        required="true"
                        error="{{ $errors->first('nama_barang') }}"
                    />

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-form.input
                                id="lokasi"
                                label="Lokasi Penempatan"
                                wire:model="lokasi"
                                placeholder="Contoh: Ruang Kepala Desa"
                                required="true"
                                error="{{ $errors->first('lokasi') }}"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="kondisi" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                            @error('kondisi') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai Aset (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" class="form-control fs-5" wire:model="nilai_aset" min="0" placeholder="0" required>
                        </div>
                        @error('nilai_aset') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Asal Pengeluaran Transaksi (Opsional)</label>
                        <select class="form-control" wire:model="id_pengeluaran">
                            <option value="">-- Tidak Terkait Transaksi --</option>
                            @foreach($pengeluarans as $pengeluaran)
                                <option value="{{ $pengeluaran->id }}">
                                    [{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d M') }}] 
                                    Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }} - 
                                    {{ Str::limit($pengeluaran->keterangan ?? 'Tanpa Keterangan', 30) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small">Pilih jika barang ini dibeli melalui transaksi pengeluaran.</div>
                        @error('id_pengeluaran') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingInventarisId ? 'Update Aset' : 'Simpan Aset' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showViewModal && $viewingInventaris)
        <div class="modal-backdrop-custom" wire:click.self="closeViewModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">Detail Inventaris</h5>
                    <button type="button" class="modal-close-btn" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-3">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nama Barang</td>
                            <td>: <span class="fw-bold">{{ $viewingInventaris->nama_barang }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kode Barang</td>
                            <td>: <span class="font-monospace text-primary">{{ $viewingInventaris->kode_barang }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lokasi</td>
                            <td>: {{ $viewingInventaris->lokasi }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Perolehan</td>
                            <td>: {{ \Carbon\Carbon::parse($viewingInventaris->tanggal_perolehan)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kondisi</td>
                            <td>: <x-ui.badge :variant="$this->getKondisiBadgeVariant($viewingInventaris->kondisi)">{{ $viewingInventaris->kondisi }}</x-ui.badge></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nilai Aset</td>
                            <td>: <span class="fw-bold text-success">{{ $this->formatRupiah($viewingInventaris->nilai_aset) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dari Pengeluaran</td>
                            <td>: 
                                @if($viewingInventaris->pengeluaran)
                                    Rp {{ number_format($viewingInventaris->pengeluaran->jumlah, 0, ',', '.') }}
                                    <br><small class="text-muted ms-2">{{ $viewingInventaris->pengeluaran->keterangan }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keterangan Tambahan</td>
                            <td>: {{ $viewingInventaris->keterangan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <x-ui.button type="button" variant="outline" wire:click="closeViewModal">Tutup</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data inventaris ini dari buku induk?"
        on-confirm="deleteInventaris"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Ya, Hapus
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
