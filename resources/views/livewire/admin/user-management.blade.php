<div>
    {{-- Page Header --}}
    <x-layout.page-header title="Kelola Pengguna" subtitle="Kelola seluruh pengguna di dalam sistem">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Pengguna
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Flash Messages --}}
    <x-ui.toast />

    {{-- Users Table Card --}}
    <x-layout.modern-card>
        {{-- Role Tabs --}}
        <x-ui.tabs variant="pills" class="mb-4">
            <li class="nav-item">
                <button class="nav-link {{ $roleType === 'admin' ? 'active' : '' }}" wire:click="$set('roleType', 'admin')">Admin</button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $roleType === 'bendahara' ? 'active' : '' }}" wire:click="$set('roleType', 'bendahara')">Bendahara</button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $roleType === 'kepala_desa' ? 'active' : '' }}" wire:click="$set('roleType', 'kepala_desa')">Kepala Desa</button>
            </li>
        </x-ui.tabs>

        {{-- Search and Filters --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold text-body">Daftar {{ ucfirst(str_replace('_', ' ', $roleType)) }}</h5>
            <div style="max-width: 300px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari {{ str_replace('_', ' ', $roleType) }}..."
                    icon="fas fa-search"
                    class="mb-0"
                />
            </div>
        </div>

        {{-- Users Table --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->{$pk} }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($user->hasAvatar())
                                        <img src="{{ $user->avatarUrl() }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="user-avatar">{{ $user->initials() }}</div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-body">{{ $user->nama }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-edit wire:click="openEditModal({{ $user->{$pk} }})" tooltip="Edit pengguna" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $user->{$pk} }})" tooltip="Hapus pengguna" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <x-ui.empty-state
                                    icon="fas fa-users"
                                    title="Pengguna tidak ditemukan"
                                    description="Coba ubah kata kunci pencarian Anda atau tambahkan pengguna baru."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </x-layout.modern-card>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingUserId ? 'Edit ' . ucfirst(str_replace('_', ' ', $roleType)) : 'Tambah ' . ucfirst(str_replace('_', ' ', $roleType)) . ' Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <x-form.input
                        id="nama"
                        label="Nama"
                        wire:model="nama"
                        placeholder="Masukkan nama lengkap"
                        required="true"
                        error="{{ $errors->first('nama') }}"
                    />

                    <x-form.input
                        type="email"
                        id="email"
                        label="Email"
                        wire:model="email"
                        placeholder="Masukkan alamat email"
                        required="true"
                        error="{{ $errors->first('email') }}"
                    />

                    <x-form.input
                        type="password"
                        id="password"
                        label="Password"
                        wire:model="password"
                        placeholder="{{ $editingUserId ? 'Masukkan password baru' : 'Masukkan password' }}"
                        required="{{ !$editingUserId }}"
                        hint="{{ $editingUserId ? '(kosongkan jika tidak ingin mengubah password saat ini)' : '' }}"
                        error="{{ $errors->first('password') }}"
                    />

                    <x-form.input
                        type="password"
                        id="password_confirmation"
                        label="Konfirmasi Password"
                        wire:model="password_confirmation"
                        placeholder="Konfirmasi password"
                    />

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeModal">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingUserId ? 'Perbarui' : 'Simpan' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan."
        on-confirm="deleteUser"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Hapus Pengguna
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
