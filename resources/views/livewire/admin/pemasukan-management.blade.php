<div>
    <x-layout.page-header title="Kelola Pemasukan" subtitle="Catat dan kelola semua dana pemasukan desa">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Catat Pemasukan
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.toast />

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pemasukan Tahun Ini</p>
                        <h3 class="fw-bold text-body mb-0">{{ $this->formatRupiah($totalTahunIni) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-calendar-alt text-success fs-4"></i>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
        <div class="col-md-4">
            <x-layout.modern-card class="border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-semibold text-uppercase small">Total Pemasukan Keseluruhan</p>
                        <h3 class="fw-bold text-body mb-0">{{ $this->formatRupiah($totalPemasukan) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-wallet text-primary fs-4"></i>
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
            <h5 class="mb-0 fw-semibold text-body">Riwayat Pemasukan</h5>
            <div style="max-width: 300px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari sumber dana atau keterangan..."
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
                        <th>Sumber Dana</th>
                        <th>Jumlah (Rp)</th>
                        <th>Keterangan</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemasukans as $pemasukan)
                        <tr wire:key="pemasukan-{{ $pemasukan->id }}">
                            <td class="text-secondary">{{ \Carbon\Carbon::parse($pemasukan->tanggal)->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <span class="fw-semibold text-body">{{ $pemasukan->sumber_dana }}</span>
                                </div>
                            </td>
                            <td class="text-success fw-bold">{{ $this->formatRupiah($pemasukan->jumlah) }}</td>
                            <td class="text-muted">{{ $pemasukan->keterangan ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-view wire:click="openViewModal({{ $pemasukan->id }})" tooltip="Detail" />
                                    <x-ui.btn-edit wire:click="openEditModal({{ $pemasukan->id }})" tooltip="Edit" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $pemasukan->id }})" tooltip="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-ui.empty-state
                                    icon="fas fa-wallet"
                                    title="Belum ada data pemasukan"
                                    description="Catat pemasukan pertama untuk memulai pembukuan."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pemasukans->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $pemasukans->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingPemasukanId ? 'Edit Pemasukan' : 'Catat Pemasukan Baru' }}
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

                    <x-form.input
                        id="sumber_dana"
                        label="Sumber Dana"
                        wire:model="sumber_dana"
                        placeholder="Contoh: Dana Desa (APBN), Retribusi"
                        required="true"
                        error="{{ $errors->first('sumber_dana') }}"
                    />

                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" class="form-control fs-5" wire:model="jumlah" min="0" placeholder="0" required>
                        </div>
                        @error('jumlah') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <x-form.input
                        id="keterangan"
                        label="Keterangan Tambahan"
                        wire:model="keterangan"
                        placeholder="Detail atau catatan tambahan (opsional)"
                        error="{{ $errors->first('keterangan') }}"
                    />

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingPemasukanId ? 'Perbarui' : 'Simpan Pemasukan' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showViewModal && $viewingPemasukan)
        <div class="modal-backdrop-custom" wire:click.self="closeViewModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">Detail Pemasukan</h5>
                    <button type="button" class="modal-close-btn" wire:click="closeViewModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-3">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Tanggal Transaksi</td>
                            <td>: <span class="fw-semibold">{{ \Carbon\Carbon::parse($viewingPemasukan->tanggal)->format('d F Y') }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sumber Dana</td>
                            <td>: <span class="fw-semibold">{{ $viewingPemasukan->sumber_dana }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jumlah</td>
                            <td>: <span class="fw-bold text-success">{{ $this->formatRupiah($viewingPemasukan->jumlah) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>: {{ $viewingPemasukan->keterangan ?? '-' }}</td>
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
        message="Apakah Anda yakin ingin menghapus catatan pemasukan ini? Hal ini dapat memengaruhi rekap keuangan."
        on-confirm="deletePemasukan"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Ya, Hapus
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
