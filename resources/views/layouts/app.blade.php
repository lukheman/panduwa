@props([
    'title' => 'Modern Admin Dashboard',
    'brandName' => 'SIWANDA',
    'brandIcon' => 'fas fa-landmark'
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css')}}">

    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css')}}">

    <link rel="stylesheet" href="{{ asset('assets/css/customize-theme.css')}}">

    @livewireStyles
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand i {
            font-size: 1.8rem;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
        }

        .menu-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            margin: 0.25rem 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-menu a i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: var(--bg-secondary);
            height: var(--topbar-height);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.3s ease;
        }

        .topbar .form-control {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .topbar .form-control::placeholder {
            color: var(--text-muted);
        }

        .topbar .input-group-text {
            background: var(--input-bg);
            border-color: var(--border-color);
        }

        .content-area {
            padding: 2rem;
        }

        .modern-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
        }

        .modern-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        [data-theme="dark"] .modern-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
        }

        .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }

        [data-theme="dark"] .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-light);
        }

        .btn-modern {
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary-modern {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary-modern:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .alert-modern {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .progress-modern {
            height: 8px;
            border-radius: 50px;
            background: var(--border-light);
        }

        .progress-bar-modern {
            border-radius: 50px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
        }

        .table-modern {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table-modern thead th {
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
        }

        .table-modern tbody tr {
            background: var(--bg-secondary);
            box-shadow: var(--card-shadow);
            border-radius: 8px;
        }

        .table-modern tbody td {
            padding: 1rem;
            border: none;
            vertical-align: middle;
            color: var(--text-primary);
        }

        .table-modern tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table-modern tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Bootstrap Table Dark Mode Override */
        .table {
            --bs-table-bg: var(--bg-secondary);
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--border-color);
            --bs-table-striped-bg: var(--bg-tertiary);
            --bs-table-striped-color: var(--text-primary);
            --bs-table-hover-bg: var(--hover-bg);
            --bs-table-hover-color: var(--text-primary);
        }

        .table > :not(caption) > * > * {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border-bottom-color: var(--border-color);
        }

        .table-modern tbody tr {
            background: var(--bg-secondary) !important;
        }

        .table-modern tbody tr:hover {
            background: var(--hover-bg) !important;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
        }

        .theme-toggle i {
            font-size: 1.25rem;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .topbar .user-name {
            color: var(--text-primary);
        }

        .topbar .user-role {
            color: var(--text-muted);
        }

        /* Modal Styles */
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content-custom {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
        }

        .modal-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title-custom {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .modal-close-btn:hover {
            color: var(--danger-color);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Input Group Dark Mode */
        .input-group-text {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
        }

        .input-group .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .input-group .form-control:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Action buttons in table */
        .action-btn {
            background: transparent;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--hover-bg);
        }

        .action-btn-edit {
            color: var(--primary-color);
        }

        .action-btn-delete {
            color: var(--danger-color);
        }

        .action-btn-view {
            color: var(--secondary-color);
        }

        /* Pagination */
        .pagination {
            --bs-pagination-bg: var(--bg-secondary);
            --bs-pagination-color: var(--text-primary);
            --bs-pagination-border-color: var(--border-color);
            --bs-pagination-hover-bg: var(--hover-bg);
            --bs-pagination-hover-color: var(--primary-color);
            --bs-pagination-focus-bg: var(--hover-bg);
            --bs-pagination-active-bg: var(--primary-color);
            --bs-pagination-active-border-color: var(--primary-color);
            --bs-pagination-disabled-bg: var(--bg-tertiary);
            --bs-pagination-disabled-color: var(--text-muted);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <x-layout.sidebar :brand-name="$brandName" :brand-icon="$brandIcon">
        @php
            $guard = Auth::guard('admin')->check() ? 'admin' :
                     (Auth::guard('bendahara')->check() ? 'bendahara' :
                     (Auth::guard('kepala_desa')->check() ? 'kepala_desa' : 'admin'));
            $routePrefix = $guard . '.';
        @endphp

        <x-layout.sidebar-section title="Main">
            <x-layout.sidebar-link href="{{ route($routePrefix . 'dashboard') }}" icon="fas fa-home" :active="request()->routeIs($routePrefix . 'dashboard')">Dashboard</x-layout.sidebar-link>
        </x-layout.sidebar-section>

        @if(Auth::guard('admin')->check())
        <x-layout.sidebar-section title="Master Data">
            <x-layout.sidebar-link href="{{ route('admin.kategori') }}" icon="fas fa-tags" :active="request()->routeIs('admin.kategori')">Kategori Transaksi</x-layout.sidebar-link>
            <x-layout.sidebar-link href="{{ route('admin.users') }}" icon="fas fa-users" :active="request()->routeIs('admin.users')">Pengguna</x-layout.sidebar-link>
        </x-layout.sidebar-section>
        @endif

        @if(Route::has($routePrefix . 'pemasukan'))
        <x-layout.sidebar-section title="Keuangan">
            <x-layout.sidebar-link href="{{ route($routePrefix . 'pemasukan') }}" icon="fas fa-wallet" :active="request()->routeIs($routePrefix . 'pemasukan')">Pemasukan</x-layout.sidebar-link>
            <x-layout.sidebar-link href="{{ route($routePrefix . 'pengeluaran') }}" icon="fas fa-hand-holding-usd" :active="request()->routeIs($routePrefix . 'pengeluaran')">Pengeluaran</x-layout.sidebar-link>
        </x-layout.sidebar-section>
        @endif


        @if(Route::has($routePrefix . 'kegiatan') || Route::has($routePrefix . 'inventaris'))
        <x-layout.sidebar-section title="Operasional & Aset">
            @if(Route::has($routePrefix . 'kegiatan'))
            <x-layout.sidebar-link href="{{ route($routePrefix . 'kegiatan') }}" icon="fas fa-tasks" :active="request()->routeIs($routePrefix . 'kegiatan')">Kegiatan</x-layout.sidebar-link>
            @endif
            @if(Route::has($routePrefix . 'inventaris'))
            <x-layout.sidebar-link href="{{ route($routePrefix . 'inventaris') }}" icon="fas fa-boxes" :active="request()->routeIs($routePrefix . 'inventaris')">Inventaris</x-layout.sidebar-link>
            @endif
            @if(Auth::guard('admin')->check())
            <x-layout.sidebar-link href="{{ route('admin.mutasi-aset') }}" icon="fas fa-exchange-alt" :active="request()->routeIs('admin.mutasi-aset')">Mutasi Aset</x-layout.sidebar-link>
            @endif
        </x-layout.sidebar-section>
        @endif

        @if(Route::has($routePrefix . 'sisa-anggaran') || Route::has($routePrefix . 'laporan-realisasi'))
        <x-layout.sidebar-section title="Laporan">
            @if(Route::has($routePrefix . 'sisa-anggaran'))
            <x-layout.sidebar-link href="{{ route($routePrefix . 'sisa-anggaran') }}" icon="fas fa-chart-pie" :active="request()->routeIs($routePrefix . 'sisa-anggaran')">Laporan Sisa Anggaran</x-layout.sidebar-link>
            @endif
            @if(Route::has($routePrefix . 'laporan-realisasi'))
            <x-layout.sidebar-link href="{{ route($routePrefix . 'laporan-realisasi') }}" icon="fas fa-file-pdf" :active="request()->routeIs($routePrefix . 'laporan-realisasi')">Laporan Realisasi</x-layout.sidebar-link>
            @endif
        </x-layout.sidebar-section>
        @endif

        <x-layout.sidebar-section title="Akun">
            <x-layout.sidebar-link href="{{ route($routePrefix . 'profile') }}" icon="fas fa-user-circle" :active="request()->routeIs($routePrefix . 'profile')">Profil</x-layout.sidebar-link>
        </x-layout.sidebar-section>

    </x-layout.sidebar>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        @php
            $userName = Auth::user()?->nama ?? 'Guest';
            $userRole = Auth::guard('admin')->check() ? 'Administrator' :
                        (Auth::guard('bendahara')->check() ? 'Bendahara' :
                        (Auth::guard('kepala_desa')->check() ? 'Kepala Desa' : 'Guest'));
        @endphp
        <x-layout.topbar
            :user-name="$userName"
            :user-role="$userRole"
            :notification-count="0"
            :show-logout="true"
        />

        <!-- Content Area -->
        <div class="content-area">
            {{ $slot }}
        </div>
    </div>

    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>


        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
    <ninja-keys placeholder="Type a command or search..."></ninja-keys>
    <script type="module" src="https://unpkg.com/ninja-keys?module"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ninja = document.querySelector('ninja-keys');
            
            const routes = [];
            
            @if(Route::has($routePrefix . 'dashboard'))
            routes.push({
                id: 'dashboard',
                title: 'Dashboard',
                hotkey: 'ctrl+D',
                icon: '<i class="fas fa-home"></i>',
                section: 'Navigation',
                handler: () => { window.location.href = "{{ route($routePrefix . 'dashboard') }}"; }
            });
            @endif
            
            @if(Route::has($routePrefix . 'pemasukan'))
            routes.push({
                id: 'pemasukan',
                title: 'Pemasukan',
                icon: '<i class="fas fa-wallet"></i>',
                section: 'Keuangan',
                handler: () => { window.location.href = "{{ route($routePrefix . 'pemasukan') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'pengeluaran'))
            routes.push({
                id: 'pengeluaran',
                title: 'Pengeluaran',
                icon: '<i class="fas fa-hand-holding-usd"></i>',
                section: 'Keuangan',
                handler: () => { window.location.href = "{{ route($routePrefix . 'pengeluaran') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'sisa-anggaran'))
            routes.push({
                id: 'sisa-anggaran',
                title: 'Laporan Sisa Anggaran',
                icon: '<i class="fas fa-chart-pie"></i>',
                section: 'Laporan',
                handler: () => { window.location.href = "{{ route($routePrefix . 'sisa-anggaran') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'laporan-realisasi'))
            routes.push({
                id: 'laporan-realisasi',
                title: 'Laporan Realisasi',
                icon: '<i class="fas fa-file-pdf"></i>',
                section: 'Laporan',
                handler: () => { window.location.href = "{{ route($routePrefix . 'laporan-realisasi') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'kegiatan'))
            routes.push({
                id: 'kegiatan',
                title: 'Kegiatan',
                icon: '<i class="fas fa-tasks"></i>',
                section: 'Operasional & Aset',
                handler: () => { window.location.href = "{{ route($routePrefix . 'kegiatan') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'inventaris'))
            routes.push({
                id: 'inventaris',
                title: 'Inventaris',
                icon: '<i class="fas fa-boxes"></i>',
                section: 'Operasional & Aset',
                handler: () => { window.location.href = "{{ route($routePrefix . 'inventaris') }}"; }
            });
            @endif

            @if(Auth::guard('admin')->check() && Route::has('admin.mutasi-aset'))
            routes.push({
                id: 'mutasi-aset',
                title: 'Mutasi Aset',
                icon: '<i class="fas fa-exchange-alt"></i>',
                section: 'Operasional & Aset',
                handler: () => { window.location.href = "{{ route('admin.mutasi-aset') }}"; }
            });
            @endif

            @if(Auth::guard('admin')->check() && Route::has('admin.kategori'))
            routes.push({
                id: 'kategori',
                title: 'Kategori Transaksi',
                icon: '<i class="fas fa-tags"></i>',
                section: 'Master Data',
                handler: () => { window.location.href = "{{ route('admin.kategori') }}"; }
            });
            @endif

            @if(Auth::guard('admin')->check() && Route::has('admin.users'))
            routes.push({
                id: 'users',
                title: 'Pengguna',
                icon: '<i class="fas fa-users"></i>',
                section: 'Master Data',
                handler: () => { window.location.href = "{{ route('admin.users') }}"; }
            });
            @endif

            @if(Route::has($routePrefix . 'profile'))
            routes.push({
                id: 'profile',
                title: 'Profil',
                icon: '<i class="fas fa-user-circle"></i>',
                section: 'Akun',
                handler: () => { window.location.href = "{{ route($routePrefix . 'profile') }}"; }
            });
            @endif
            


            ninja.data = routes;
        });
    </script>
    @livewireScripts
</body>
</html>
