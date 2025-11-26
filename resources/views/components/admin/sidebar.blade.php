<div class="container-fluid grow d-flex">
    {{-- Sidebar Navigasi Admin --}}
    <div class="sidebar">
        <h5 class="text-white mb-4">Navigasi Admin</h5>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.kamars.*') ? 'active' : '' }}"
                    href="{{ route('admin.kamars.index') }}">
                    <i class="fas fa-bed me-2"></i> Manajemen Kamar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.tipe_kamars.*') ? 'active' : '' }}"
                    href="{{ route('admin.tipe_kamars.index') }}">
                    <i class="fas fa-hotel me-2"></i> Manajemen Tipe Kamar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.pemesanans.*') ? 'active' : '' }}"
                    href="{{ route('admin.pemesanans.index') }}">
                    <i class="fas fa-receipt me-2"></i> Manajemen Pemesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users me-2"></i> Manajemen Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.fasilitas.*') ? 'active' : '' }}"
                    href="{{ route('admin.fasilitas.index') }}">
                    <i class="fas fa-spa me-2"></i> Manajemen Fasilitas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.riwayat.transaksi') ? 'active' : '' }}"
                    href="{{ route('admin.riwayat.transaksi') }}">
                    <i class="fas fa-history me-2"></i> Riwayat Transaksi
                </a>
            </li>
        </ul>
    </div>
