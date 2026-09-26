<div>
    <x-layout.page-header title="Kelola Bidang & Sub-Bidang" subtitle="Setiap bidang dapat memiliki banyak sub-bidang, dan setiap kegiatan terkait ke satu sub-bidang">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <x-ui.button variant="secondary" icon="fas fa-plus" wire:click="openCreateSubModal">
                    Tambah Sub-Bidang
                </x-ui.button>
                <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateBidangModal">
                    Tambah Bidang
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.toast />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-semibold text-body">Daftar Bidang Kegiatan</h5>
        <div style="max-width: 300px; width: 100%;">
            <x-form.input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari bidang / sub-bidang..."
                icon="fas fa-search"
                class="mb-0"
            />
        </div>
    </div>

    <div class="d-flex flex-column gap-3 mb-4">
        @forelse ($bidangs as $bidang)
            <x-layout.modern-card class="p-0 overflow-hidden" wire:key="bidang-{{ $bidang->id }}">
                <div class="p-4 d-flex justify-content-between align-items-center" style="cursor: pointer;" wire:click="toggleExpand({{ $bidang->id }})">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 48px; height: 48px;">
                            {{ $bidang->kode }}
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-body">{{ $bidang->nama }}</h6>
                            <div class="small text-muted">
                                {{ $bidang->sub_bidangs_count }} sub-bidang &bull; {{ $bidang->kegiatans_count }} kegiatan
                                @if($bidang->deskripsi)
                                    &bull; {{ \Illuminate\Support\Str::limit($bidang->deskripsi, 80) }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2" wire:click.stop>
                        <x-ui.button variant="outline" size="sm" icon="fas fa-plus" wire:click="openCreateSubModal({{ $bidang->id }})">
                            Sub
                        </x-ui.button>
                        <x-ui.btn-edit wire:click="openEditBidangModal({{ $bidang->id }})" tooltip="Edit Bidang" />
                        <x-ui.btn-delete wire:click="confirmDeleteBidang({{ $bidang->id }})" tooltip="Hapus Bidang" />
                        <i class="fas fa-chevron-{{ in_array($bidang->id, $expandedBidang) ? 'up' : 'down' }} text-muted ms-2"></i>
                    </div>
                </div>

                @if(in_array($bidang->id, $expandedBidang) || $search)
                    <div class="border-top bg-light px-4 py-3">
                        @if($bidang->subBidangs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Sub-Bidang</th>
                                            <th class="text-center">Kegiatan</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bidang->subBidangs as $sub)
                                            <tr wire:key="sub-{{ $sub->id }}">
                                                <td><span class="badge bg-secondary">{{ $sub->kode }}</span></td>
                                                <td class="fw-medium text-body">{{ $sub->nama }}</td>
                                                <td class="text-center"><span class="badge bg-info">{{ $sub->kegiatans->count() }} kegiatan</span></td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <x-ui.btn-edit wire:click="openEditSubModal({{ $sub->id }})" tooltip="Edit Sub-Bidang" />
                                                        <x-ui.btn-delete wire:click="confirmDeleteSub({{ $sub->id }})" tooltip="Hapus Sub-Bidang" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted small mb-0 py-2"><i class="fas fa-info-circle me-1"></i>Belum ada sub-bidang pada bidang ini.</p>
                        @endif
                    </div>
                @endif
            </x-layout.modern-card>
        @empty
            <x-layout.modern-card class="text-center py-5">
                <x-ui.empty-state
                    icon="fas fa-layer-group"
                    title="Belum ada bidang kegiatan"
                    description="Tambahkan bidang baru sesuai Permendagri 20/2018, lalu tambahkan sub-bidang di dalamnya."
                />
            </x-layout.modern-card>
        @endforelse
    </div>

    @if ($bidangs->hasPages())
        <div class="d-flex justify-content-center">
            {{ $bidangs->links() }}
        </div>
    @endif

    {{-- Modal Bidang --}}
    @if ($showBidangModal)
        <div class="modal-backdrop-custom" wire:click.self="closeBidangModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">{{ $editingBidangId ? 'Edit Bidang' : 'Tambah Bidang Baru' }}</h5>
                    <button type="button" class="modal-close-btn" wire:click="closeBidangModal"><i class="fas fa-times"></i></button>
                </div>
                <form wire:submit="saveBidang">
                    <div class="mb-3">
                        <label class="form-label">Kode Bidang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="bidang_kode" placeholder="Contoh: 01" required>
                        @error('bidang_kode') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Bidang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="bidang_nama" placeholder="Contoh: Pelaksanaan Pembangunan Desa" required>
                        @error('bidang_nama') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" wire:model="bidang_deskripsi" rows="3" placeholder="Deskripsi singkat (opsional)"></textarea>
                        @error('bidang_deskripsi') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeBidangModal">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="primary">{{ $editingBidangId ? 'Update' : 'Simpan' }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Sub-Bidang --}}
    @if ($showSubModal)
        <div class="modal-backdrop-custom" wire:click.self="closeSubModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">{{ $editingSubId ? 'Edit Sub-Bidang' : 'Tambah Sub-Bidang Baru' }}</h5>
                    <button type="button" class="modal-close-btn" wire:click="closeSubModal"><i class="fas fa-times"></i></button>
                </div>
                <form wire:submit="saveSub">
                    <div class="mb-3">
                        <label class="form-label">Bidang Induk <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="sub_bidang_kegiatan_id" required>
                            <option value="">-- Pilih Bidang --</option>
                            @foreach($allBidangs as $b)
                                <option value="{{ $b->id }}">{{ $b->kode }} - {{ $b->nama }}</option>
                            @endforeach
                        </select>
                        @error('sub_bidang_kegiatan_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Sub-Bidang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="sub_kode" placeholder="Contoh: 02.01" required>
                        @error('sub_kode') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Sub-Bidang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="sub_nama" placeholder="Contoh: Pendidikan" required>
                        @error('sub_nama') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" wire:model="sub_deskripsi" rows="3" placeholder="Deskripsi singkat (opsional)"></textarea>
                        @error('sub_deskripsi') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeSubModal">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="primary">{{ $editingSubId ? 'Update' : 'Simpan' }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteBidangModal"
        title="Hapus Bidang?"
        message="Bidang beserta seluruh sub-bidang di dalamnya akan dihapus. Lanjut?"
        on-confirm="deleteBidang"
        on-cancel="cancelDeleteBidang"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton><i class="fas fa-trash-alt me-2"></i>Ya, Hapus</x-slot:confirmButton>
    </x-ui.confirm-modal>

    <x-ui.confirm-modal
        :show="$showDeleteSubModal"
        title="Hapus Sub-Bidang?"
        message="Sub-bidang akan dihapus permanen. Kegiatan yang terkait tidak boleh ada."
        on-confirm="deleteSub"
        on-cancel="cancelDeleteSub"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton><i class="fas fa-trash-alt me-2"></i>Ya, Hapus</x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
