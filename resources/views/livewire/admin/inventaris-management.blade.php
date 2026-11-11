<div>
    <x-layout.page-header title="Kelola Inventaris" subtitle="Buku Induk Aset dan Barang Inventaris Desa">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Inventaris
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.toast />

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Unit Barang/Aset</p>
                        <h3 class="fw-bold text-body mb-0">{{ $totalAset }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
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
                        <h3 class="fw-bold text-body mb-0">{{ $asetKondisiBaik }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
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
                    placeholder="Cari kode atau nama barang..."
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
                        <th class="text-center">Jumlah</th>
                        <th>Sumber Dana</th>
                        <th>Nilai Aset (Rp)</th>
                        <th>Kondisi</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventarises as $inventaris)
                        <tr wire:key="inventaris-{{ $inventaris->id }}">
                            <td>
                                <div class="fw-semibold text-body">{{ $inventaris->nama_barang }}</div>
                                <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>{{ $inventaris->kode_barang }}</small>
                                @if ($inventaris->keterangan)
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($inventaris->keterangan, 50) }}</div>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $inventaris->jumlah }}</td>
                            <td>
                                <x-ui.badge variant="primary" icon="fas fa-wallet">
                                    {{ $inventaris->sumberDana?->nama ?? '-' }}
                                </x-ui.badge>
                                @if ($inventaris->pengeluaran)
                                    <div class="small text-muted mt-1"><i class="fas fa-link me-1"></i>Pengeluaran bendahara</div>
                                @endif
                            </td>
                            <td class="text-success fw-bold">{{ $this->formatRupiah($inventaris->nilai_aset) }}</td>
                            <td>
                                <x-ui.badge :variant="$this->getKondisiBadgeVariant($inventaris->kondisi)">
                                    {{ $inventaris->kondisi->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-view wire:click="openViewModal({{ $inventaris->id }})" tooltip="Detail" />
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
            <div class="modal-content-custom" wire:click.stop style="max-height: 90vh; overflow-y: auto; max-width: 720px;">
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

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <x-form.input
                                id="nama_barang"
                                label="Nama Barang"
                                wire:model="nama_barang"
                                placeholder="Contoh: Kipas angin, Laptop Asus VivoBook, Kursi Rapat"
                                required="true"
                                error="{{ $errors->first('nama_barang') }}"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah Barang <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" wire:model="jumlah" min="1" placeholder="1" required>
                            @error('jumlah') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sumber Dana / Asal Uang <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="sumber_dana_id" required>
                            <option value="">-- Pilih sumber dana --</option>
                            @foreach ($sumberDanas as $sumber)
                                <option value="{{ $sumber->id }}">{{ $sumber->kode }} - {{ $sumber->nama }}</option>
                            @endforeach
                        </select>
                        @error('sumber_dana_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Contoh: pembelian kipas angin berasal dari Dana Desa atau Alokasi Dana Desa.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hubungkan ke Pengeluaran Bendahara (Opsional)</label>
                        <select class="form-control" wire:model.live="id_pengeluaran">
                            <option value="">-- Tidak dihubungkan --</option>
                            @foreach ($pengeluarans as $pengeluaran)
                                <option value="{{ $pengeluaran->id }}">
                                    {{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d/m/Y') }} -
                                    {{ $pengeluaran->sumberDana?->nama ?? 'Tanpa sumber' }} -
                                    {{ $this->formatRupiah($pengeluaran->jumlah) }} -
                                    {{ \Illuminate\Support\Str::limit($pengeluaran->keterangan ?: 'Pengeluaran', 40) }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_pengeluaran') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Kaur Umum dapat menambahkan inventaris, lalu menghubungkannya dengan pengeluaran yang dicatat bendahara.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="kondisi" required>
                                @foreach (\App\Enums\KondisiInventaris::cases() as $kondisi)
                                    <option value="{{ $kondisi->value }}">{{ $kondisi->getLabel() }}</option>
                                @endforeach
                            </select>
                            @error('kondisi') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nilai Aset / Total Pembelian (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" class="form-control" wire:model="nilai_aset" min="0" placeholder="0" required>
                            </div>
                            @error('nilai_aset') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" rows="2" wire:model="keterangan" placeholder="Contoh: Pembelian 2 unit kipas angin untuk balai desa"></textarea>
                        @error('keterangan') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeModal">
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
                            <td class="text-muted">Jumlah</td>
                            <td>: <span class="fw-bold">{{ $viewingInventaris->jumlah }} unit</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sumber Dana</td>
                            <td>: <x-ui.badge variant="primary" icon="fas fa-wallet">{{ $viewingInventaris->sumberDana?->nama ?? '-' }}</x-ui.badge></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Perolehan</td>
                            <td>: {{ \Carbon\Carbon::parse($viewingInventaris->tanggal_perolehan)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kondisi</td>
                            <td>: <x-ui.badge :variant="$this->getKondisiBadgeVariant($viewingInventaris->kondisi)">{{ $viewingInventaris->kondisi->getLabel() }}</x-ui.badge></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nilai Aset</td>
                            <td>: <span class="fw-bold text-success">{{ $this->formatRupiah($viewingInventaris->nilai_aset) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pengeluaran Bendahara</td>
                            <td>:
                                @if ($viewingInventaris->pengeluaran)
                                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($viewingInventaris->pengeluaran->tanggal)->format('d F Y') }} - {{ $this->formatRupiah($viewingInventaris->pengeluaran->jumlah) }}</span>
                                    <div class="small text-muted">{{ $viewingInventaris->pengeluaran->keterangan ?: '-' }}</div>
                                @else
                                    <span class="text-muted">Belum dihubungkan</span>
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
                    <x-ui.button type="button" variant="secondary" wire:click="closeViewModal">Tutup</x-ui.button>
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
