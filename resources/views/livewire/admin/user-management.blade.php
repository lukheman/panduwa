<div>
    {{-- Page Header --}}
    <x-layout.page-header title="User Management" subtitle="Manage all users in the system">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Add User
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-ui.alert variant="success" title="Success!" class="mb-4">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" title="Error!" class="mb-4">
            {{ session('error') }}
        </x-ui.alert>
    @endif

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
            <h5 class="mb-0 fw-semibold text-body">List of {{ ucfirst(str_replace('_', ' ', $roleType)) }}</h5>
            <div style="max-width: 300px; width: 100%;">
                <x-form.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search {{ str_replace('_', ' ', $roleType) }}..."
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
                        <th>User</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th style="width: 120px;">Actions</th>
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
                                        <small class="text-muted">ID: {{ $user->{$pk} }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <x-ui.btn-edit wire:click="openEditModal({{ $user->{$pk} }})" tooltip="Edit user" />
                                    <x-ui.btn-delete wire:click="confirmDelete({{ $user->{$pk} }})" tooltip="Delete user" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <x-ui.empty-state
                                    icon="fas fa-users"
                                    title="No users found"
                                    description="Try adjusting your search query or add a new user."
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
                        {{ $editingUserId ? 'Edit ' . ucfirst(str_replace('_', ' ', $roleType)) : 'Create New ' . ucfirst(str_replace('_', ' ', $roleType)) }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <x-form.input
                        id="nama"
                        label="Name"
                        wire:model="nama"
                        placeholder="Enter full name"
                        required="true"
                        error="{{ $errors->first('nama') }}"
                    />

                    <x-form.input
                        type="email"
                        id="email"
                        label="Email"
                        wire:model="email"
                        placeholder="Enter email address"
                        required="true"
                        error="{{ $errors->first('email') }}"
                    />

                    <x-form.input
                        type="password"
                        id="password"
                        label="Password"
                        wire:model="password"
                        placeholder="{{ $editingUserId ? 'Enter new password' : 'Enter password' }}"
                        required="{{ !$editingUserId }}"
                        hint="{{ $editingUserId ? '(leave blank to keep current)' : '' }}"
                        error="{{ $errors->first('password') }}"
                    />

                    <x-form.input
                        type="password"
                        id="password_confirmation"
                        label="Confirm Password"
                        wire:model="password_confirmation"
                        placeholder="Confirm password"
                    />

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-ui.button type="button" variant="outline" wire:click="closeModal">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            {{ $editingUserId ? 'Update' : 'Create' }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-ui.confirm-modal
        :show="$showDeleteModal"
        title="Confirm Delete"
        message="Are you sure you want to delete this user? This action cannot be undone."
        on-confirm="deleteUser"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Delete User
        </x-slot:confirmButton>
    </x-ui.confirm-modal>
</div>
