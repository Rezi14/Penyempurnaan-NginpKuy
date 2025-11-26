@extends('layouts.user.app')

@section('title', 'Profil Saya - Roomify')

@section('content')
    <div class="container py-5">

        {{-- Menampilkan Pesan Sukses/Error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm text-center p-4 h-100">
                    <div class="mb-4 d-flex justify-content-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <div class="mb-4">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill border border-primary">
                            {{ $user->nama_role ?? 'Member' }}
                        </span>
                    </div>

                    <hr class="my-4">

                    <div class="text-start">
                        <p class="small text-muted mb-2 fw-bold">PENGATURAN AKUN</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-light text-start" data-bs-toggle="modal"
                                data-bs-target="#editProfileModal">
                                <i class="fas fa-user-edit me-2 text-primary"></i> Edit Profil
                            </button>

                            <button class="btn btn-light text-start" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <i class="fas fa-key me-2 text-primary"></i> Ubah Password
                            </button>

                            <form action="{{ route('logout') }}" method="POST" class="d-grid mt-3">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-history me-2"></i> Riwayat Pemesanan
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($user->pemesanans && $user->pemesanans->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kamar</th>
                                            <th>Check-in</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->pemesanans as $pemesanan)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">
                                                        {{ $pemesanan->kamar->tipeKamar->nama_tipe ?? 'Kamar' }}</div>
                                                    <small class="text-muted">No.
                                                        {{ $pemesanan->kamar->nomor_kamar }}</small>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($pemesanan->tanggal_check_in)->format('d M Y') }}
                                                </td>
                                                <td class="fw-bold">Rp
                                                    {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td>
                                                <td>
                                                    <span
                                                        class="badge rounded-pill px-3
                                                    {{ $pemesanan->status_pemesanan == 'confirmed'
                                                        ? 'bg-success'
                                                        : ($pemesanan->status_pemesanan == 'pending'
                                                            ? 'bg-warning text-dark'
                                                            : 'bg-secondary') }}">
                                                        {{ ucfirst($pemesanan->status_pemesanan) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    @if ($pemesanan->status_pemesanan == 'pending')
                                                        <a href="{{ route('booking.payment', $pemesanan->id) }}"
                                                            class="btn btn-sm btn-primary fw-bold">
                                                            Bayar
                                                        </a>
                                                    @else
                                                        {{-- UBAH BAGIAN INI --}}
                                                        <a href="{{ route('booking.detail', $pemesanan->id) }}"
                                                            class="btn btn-sm btn-outline-primary border">
                                                            Detail
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <p class="text-muted">Belum ada riwayat pemesanan.</p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Cari Kamar</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editName" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Ubah Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="password" required
                                minlength="8">
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
