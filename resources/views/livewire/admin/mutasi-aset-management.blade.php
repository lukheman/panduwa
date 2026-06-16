<div>
    <x-layout.page-header title="Mutasi Aset" subtitle="Pencatatan riwayat perubahan status kepemilikan atau kondisi aset">
        <x-slot:actions>
            <x-ui.button variant="warning" icon="fas fa-exchange-alt" wire:click="openCreateModal">
                Catat Mutasi
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))
        <x-ui.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-layout.modern-card>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold text-body">Riwayat Mutasi Aset</h5>
            <div style="max-width: 350px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari aset atau jenis mutasi..."
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
                        <th>Barang / Aset</th>
                        <th>Jenis Mutasi</th>
                        <th>Keterangan</th>
                        <th>Penanggung Jawab</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mutasis as $mutasi)
                        <tr wire:key="mutasi-{{ $mutasi->id }}">
                            <td class="text-secondary">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold text-body">{{ $mutasi->inventaris->nama_barang ?? 'Aset Tidak Ditemukan' }}</div>
                                @if($mutasi->inventaris)
                                    <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>{{ $mutasi->inventaris->kode_barang }}</small>
                                @endif
                            </td>
                            <td>
                                <x-ui.badge :variant="$this->getMutasiBadgeVariant($mutasi->jenis_mutasi)">
                                    {{ $mutasi->jenis_mutasi }}
                                </x-ui.badge>
                            </td>
                            <td class="text-muted">{{ Str::limit($mutasi->keterangan ?? '-', 40) }}</td>
                            <td class="text-secondary">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width: 24px; height: 24px; font-size: 0.6rem;">
                                        {{ substr($mutasi->bendahara->nama ?? 'U', 0, 2) }}
                                    </div>
                                    <small>{{ $mutasi->bendahara->nama ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-edit wire:click="openEditModal({{ $mutasi->id }})" tooltip="Edit Mutasi" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $mutasi->id }})" tooltip="Hapus Catatan" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <x-ui.empty-state
                                    icon="fas fa-history"
                                    title="Belum ada catatan mutasi"
                                    description="Catat riwayat mutasi jika ada aset yang dijual, dihibahkan, atau dimusnahkan."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutasis->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $mutasis->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop style="max-height: 90vh; overflow-y: auto;">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingMutasiId ? 'Edit Catatan Mutasi' : 'Catat Mutasi Aset Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Pilih Aset / Barang <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="id_inventaris" required {{ $editingMutasiId ? 'disabled' : '' }}>
                            <option value="">-- Pilih Aset dari Buku Induk --</option>
                            @foreach($inventarises as $inventaris)
                                <option value="{{ $inventaris->id }}">
                                    [{{ $inventaris->kode_barang }}] {{ $inventaris->nama_barang }} - Kondisi: {{ $inventaris->kondisi }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small">Aset yang dimutasi.</div>
                        @error('id_inventaris') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Mutasi <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="jenis_mutasi" required>
                                <option value="Dijual">Dijual</option>
                                <option value="Dihibahkan">Dihibahkan</option>
                                <option value="Dipindah Tangankan">Dipindah Tangankan</option>
                                <option value="Dimusnahkan">Dimusnahkan (Rusak Total)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            @error('jenis_mutasi') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="tanggal" required>
                            @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bendahara Bertugas <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="id_bendahara" required>
                            <option value="">Pilih Bendahara...</option>
                            @foreach($bendaharas as $bendahara)
                                <option value="{{ $bendahara->id }}">{{ $bendahara->nama }}</option>
                            @endforeach
                        </select>
                        @error('id_bendahara') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <x-form.input
                        id="keterangan"
                        label="Keterangan / Alasan Mutasi"
                        wire:model="keterangan"
                        placeholder="Detail kepada siapa barang dijual/dihibahkan, atau alasan pemusnahan"
                        error="{{ $errors->first('keterangan') }}"
                    />

                    @if(!$editingMutasiId)
                        <div class="alert alert-warning mt-3 py-2 px-3 small d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Mencatat mutasi ini akan mengubah status kondisi barang di buku induk inventaris secara otomatis.
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="warning">
                            <i class="fas fa-exchange-alt me-1"></i> {{ $editingMutasiId ? 'Update Data' : 'Proses Mutasi' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Batalkan Mutasi"
        message="Apakah Anda yakin ingin menghapus catatan mutasi ini? Perhatikan bahwa ini tidak mengembalikan kondisi barang otomatis di buku induk."
        on-confirm="deleteMutasi"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-undo"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Hapus Catatan
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
