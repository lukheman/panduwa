<div>
    <x-layout.page-header title="Kategori Transaksi" subtitle="Manage kategori transaksi untuk keuangan desa">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Kategori
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))
        <x-ui.alert variant="success" title="Success!" class="mb-4">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-layout.modern-card>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold text-body">Daftar Kategori</h5>
            <div style="max-width: 300px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari kategori..."
                    icon="fas fa-search"
                    class="mb-0"
                />
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                        <tr wire:key="kategori-{{ $kategori->id }}">
                            <td>{{ $kategori->id }}</td>
                            <td class="fw-semibold text-body">{{ $kategori->nama_kategori }}</td>
                            <td class="text-secondary">{{ $kategori->deskripsi ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-edit wire:click="openEditModal({{ $kategori->id }})" tooltip="Edit" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $kategori->id }})" tooltip="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <x-ui.empty-state
                                    icon="fas fa-tags"
                                    title="Tidak ada kategori"
                                    description="Silakan tambah kategori transaksi baru."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($kategoris->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $kategoris->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingKategoriId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <x-form.input
                        id="nama_kategori"
                        label="Nama Kategori"
                        wire:model="nama_kategori"
                        placeholder="Masukkan nama kategori"
                        required="true"
                        error="{{ $errors->first('nama_kategori') }}"
                    />

                    <x-form.input
                        id="deskripsi"
                        label="Deskripsi"
                        wire:model="deskripsi"
                        placeholder="Masukkan deskripsi (opsional)"
                        error="{{ $errors->first('deskripsi') }}"
                    />

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingKategoriId ? 'Update' : 'Simpan' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus kategori ini? Tindakan ini tidak dapat dibatalkan."
        on-confirm="deleteKategori"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Hapus
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
