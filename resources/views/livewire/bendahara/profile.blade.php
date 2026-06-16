<div>
    {{-- Page Header --}}
    <x-layout.page-header title="Profile Bendahara" subtitle="Kelola informasi akun Anda">
        <x-slot:actions>
            <x-ui.badge variant="success" icon="fas fa-user-check">
                Terverifikasi
            </x-ui.badge>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-ui.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" title="Error!" class="mb-4">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div class="row g-4">
        {{-- Profile Info Card --}}
        <div class="col-lg-4">
            <x-layout.modern-card class="text-center">
                {{-- Avatar Section --}}
                <div class="position-relative d-inline-block mb-3">
                    @if($currentAvatar)
                        <img src="{{ Storage::disk('public')->url($currentAvatar) }}" alt="Avatar" class="rounded-circle border border-4 border-primary"
                            style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="user-avatar mx-auto" style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ Auth::guard('bendahara')->user()->initials() ?? 'B' }}
                        </div>
                    @endif
                </div>

                <h4 class="text-body fw-semibold">{{ Auth::guard('bendahara')->user()->nama }}</h4>
                <p class="text-muted mb-3">{{ Auth::guard('bendahara')->user()->email }}</p>
                <x-ui.badge variant="primary" icon="fas fa-user-tie">Bendahara Desa</x-ui.badge>

                <hr class="my-4">

                <div class="text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bergabung</span>
                        <span class="text-body">{{ Auth::guard('bendahara')->user()->created_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Terakhir diperbarui</span>
                        <span class="text-body">{{ Auth::guard('bendahara')->user()->updated_at?->diffForHumans() ?? '-' }}</span>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>

        {{-- Edit Profile Card --}}
        <div class="col-lg-8">
            {{-- Avatar Upload --}}
            <x-layout.modern-card class="mb-4">
                <div class="preview-title d-flex align-items-center gap-2">
                    <i class="fas fa-camera text-secondary"></i>
                    Foto Profil
                </div>

                <div class="d-flex align-items-center gap-4">
                    {{-- Preview --}}
                    <div class="position-relative">
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="rounded-circle border border-3 border-primary"
                                style="width: 80px; height: 80px; object-fit: cover;">
                        @elseif($currentAvatar)
                            <img src="{{ Storage::disk('public')->url($currentAvatar) }}" alt="Avatar" class="rounded-circle border border-3 border-secondary"
                                style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="user-avatar" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ Auth::guard('bendahara')->user()->initials() ?? 'B' }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-grow-1">
                        <input type="file" wire:model="avatar" id="avatar-upload" class="d-none" accept="image/*">

                        <div class="d-flex gap-2 flex-wrap">
                            <label for="avatar-upload" class="btn btn-modern btn-primary-modern"
                                style="cursor: pointer;">
                                <i class="fas fa-upload me-2"></i>
                                <span wire:loading.remove wire:target="avatar">Pilih Foto</span>
                                <span wire:loading wire:target="avatar">Mengupload...</span>
                            </label>

                            @if($avatar)
                                <x-ui.button type="button" variant="success" wire:click="uploadAvatar" icon="fas fa-check">
                                    Simpan
                                </x-ui.button>
                                <x-ui.button type="button" variant="secondary" wire:click="$set('avatar', null)" icon="fas fa-times">
                                    Batal
                                </x-ui.button>
                            @endif

                            @if($currentAvatar && !$avatar)
                                <x-ui.button type="button" variant="danger" wire:click="removeAvatar" icon="fas fa-trash" onclick="return confirm('Hapus foto profil?')">
                                    Hapus
                                </x-ui.button>
                            @endif
                        </div>

                        @error('avatar')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror

                        <p class="text-muted mb-0 mt-2 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Format: JPG, PNG, GIF. Maksimal 2MB.
                        </p>
                    </div>
                </div>
            </x-layout.modern-card>

            {{-- Profile Information --}}
            <x-layout.modern-card class="mb-4">
                <div class="preview-title d-flex align-items-center gap-2">
                    <i class="fas fa-user-edit text-primary"></i>
                    Informasi Profile
                </div>

                <form wire:submit="updateProfile">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-form.input
                                id="nama"
                                label="Nama Lengkap"
                                wire:model="nama"
                                placeholder="Masukkan nama lengkap"
                                required="true"
                                error="{{ $errors->first('nama') }}"
                            />
                        </div>

                        <div class="col-md-6">
                            <x-form.input
                                type="email"
                                id="email"
                                label="Email"
                                wire:model="email"
                                placeholder="Masukkan email"
                                required="true"
                                error="{{ $errors->first('email') }}"
                            />
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <x-ui.button type="submit" variant="primary" icon="fas fa-save">
                            Simpan Perubahan
                        </x-ui.button>
                    </div>
                </form>
            </x-layout.modern-card>

            {{-- Change Password --}}
            <x-layout.modern-card>
                <div class="preview-title d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-lock text-warning"></i>
                        Ubah Password
                    </div>
                    <x-ui.button type="button" variant="{{ $showPasswordSection ? 'danger' : 'outline' }}" size="sm"
                        wire:click="togglePasswordSection">
                        {{ $showPasswordSection ? 'Batal' : 'Ubah Password' }}
                    </x-ui.button>
                </div>

                @if($showPasswordSection)
                    <form wire:submit="updatePassword" class="mt-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <x-form.input
                                    type="password"
                                    id="current_password"
                                    label="Password Saat Ini"
                                    wire:model="current_password"
                                    placeholder="Masukkan password saat ini"
                                    required="true"
                                    error="{{ $errors->first('current_password') }}"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-form.input
                                    type="password"
                                    id="password"
                                    label="Password Baru"
                                    wire:model="password"
                                    placeholder="Masukkan password baru"
                                    required="true"
                                    error="{{ $errors->first('password') }}"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-form.input
                                    type="password"
                                    id="password_confirmation"
                                    label="Konfirmasi Password"
                                    wire:model="password_confirmation"
                                    placeholder="Konfirmasi password baru"
                                    required="true"
                                />
                            </div>
                        </div>

                        <x-ui.alert variant="info" class="mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Password harus minimal 8 karakter dan mengandung huruf dan angka.
                        </x-ui.alert>

                        <div class="d-flex justify-content-end mt-4">
                            <x-ui.button type="submit" variant="warning" icon="fas fa-key">
                                Perbarui Password
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Klik tombol "Ubah Password" untuk memperbarui password Anda.
                    </p>
                @endif
            </x-layout.modern-card>
        </div>
    </div>
</div>
