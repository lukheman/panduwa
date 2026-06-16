<div>
    {{-- Page Header --}}
    <x-layout.page-header title="Dashboard Sistem" subtitle="Selamat datang, {{ Auth::user()->nama }}! Berikut adalah ringkasan data sistem PANDUWA hari ini.">
        <x-slot:actions>
            <a href="{{ route('admin.users') }}" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-users-cog me-2"></i>Kelola Pengguna
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <x-layout.stat-card icon="fas fa-users" label="Total Pengguna" value="{{ number_format($totalUsers) }}"
                trend-value="Keseluruhan" trend-direction="up" variant="primary" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-layout.stat-card icon="fas fa-tags" label="Kategori Transaksi" value="{{ number_format($totalKategori) }}"
                trend-value="Aktif" trend-direction="up" variant="info" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-layout.stat-card icon="fas fa-briefcase" label="Total Kegiatan" value="{{ number_format($totalKegiatan) }}"
                trend-value="Pelaksanaan" trend-direction="up" variant="success" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-layout.stat-card icon="fas fa-boxes" label="Total Inventaris" value="{{ number_format($totalInventaris) }}"
                trend-value="Aset Desa" trend-direction="up" variant="warning" />
        </div>
    </div>

    {{-- Financial Overview (for context, although managed by Bendahara) --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <x-layout.modern-card class="h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-semibold mb-0 text-body">Total Pemasukan</h5>
                    <div class="icon-circle bg-success bg-opacity-10 text-success">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-2 text-success">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h2>
                <p class="text-muted mb-0 small">Seluruh dana masuk desa</p>
            </x-layout.modern-card>
        </div>
        <div class="col-md-6">
            <x-layout.modern-card class="h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-semibold mb-0 text-body">Total Pengeluaran</h5>
                    <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-2 text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h2>
                <p class="text-muted mb-0 small">Seluruh dana keluar desa</p>
            </x-layout.modern-card>
        </div>
    </div>

    {{-- Recent Users Table --}}
    <x-layout.table-card title="Pengguna Sistem Terbaru" view-all-href="{{ route('admin.users') }}" :headers="['Nama', 'Email', 'Role', 'Tanggal Bergabung']">
        @forelse($recentUsers as $user)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            {{ strtoupper(substr($user->nama, 0, 2)) }}
                        </div>
                        <strong style="color: #1e293b;">{{ $user->nama }}</strong>
                    </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->role === 'Admin')
                        <x-ui.badge variant="primary" icon="fas fa-user-shield">{{ $user->role }}</x-ui.badge>
                    @elseif($user->role === 'Bendahara')
                        <x-ui.badge variant="success" icon="fas fa-wallet">{{ $user->role }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="info" icon="fas fa-user-tie">{{ $user->role }}</x-ui.badge>
                    @endif
                </td>
                <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-3 text-secondary"></i>
                    <p class="mb-0">Belum ada data pengguna</p>
                </td>
            </tr>
        @endforelse
    </x-layout.table-card>
</div>