<div class="login-container">
    <div class="login-card">
        <!-- Brand Logo -->
        <div class="brand-logo">
            <div class="icon-wrapper">
                <i class="fas fa-landmark"></i>
            </div>
            <h1>Selamat Datang</h1>
            <p>Silakan masuk ke SIWANDA</p>
        </div>

        <!-- Login Form -->
        <form wire:submit="submit">
            <!-- Role Selection Tabs -->
            <x-ui.tabs variant="underline" class="mb-4 nav-justified gap-2">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $role === 'admin' ? 'active' : '' }}" wire:click="$set('role', 'admin')" type="button" role="tab">Admin</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $role === 'bendahara' ? 'active' : '' }}" wire:click="$set('role', 'bendahara')" type="button" role="tab">Bendahara</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $role === 'kepala_desa' ? 'active' : '' }}" wire:click="$set('role', 'kepala_desa')" type="button" role="tab">Kades</button>
                </li>
            </x-ui.tabs>

            <!-- Email Field -->
            <div class="form-floating position-relative mt-2">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror"
                    id="email" placeholder="Alamat Email" autofocus>
                <label for="email">Alamat Email</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-floating position-relative mb-4">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" wire:model="password"
                    class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Kata Sandi">
                <label for="password">Kata Sandi</label>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <script>
                function togglePassword() {
                    const input = document.getElementById('password');
                    const icon = document.getElementById('toggleIcon');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }
            </script>

            <!-- Login Button -->
            <button type="submit" class="btn btn-login" wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk <i class="fas fa-sign-in-alt"></i></span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin me-2"></i> Memproses...
                </span>
            </button>
        </form>
    </div>
</div>
