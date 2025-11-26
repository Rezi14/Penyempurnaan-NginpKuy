@extends('layouts.admin.app')

@section('title', 'Manajemen Pemesanan - NginapKuy Admin')

@section('content')
    <div class="container-fluid px-4">
        {{-- Alert Pesan Sukses/Error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Header Halaman --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Manajemen Pemesanan</h2>
            <a href="{{ route('admin.pemesanans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Tambah Pemesanan Baru
            </a>
        </div>

        {{-- Tabel Daftar Pemesanan --}}
        <div class="card p-4 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                {{-- Kolom ID Pemesanan Dihapus --}}
                                <th>Pelanggan</th>
                                <th>Kamar</th>
                                <th>Tipe Kamar</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Total Harga</th>
                                <th>Fasilitas Tambahan</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pemesanans as $pemesanan)
                                <tr>
                                    {{-- Kolom ID Pemesanan Dihapus --}}
                                    <td>{{ $pemesanan->user->name ?? 'N/A' }}</td>
                                    <td>{{ $pemesanan->kamar->nomor_kamar ?? 'N/A' }}</td>
                                    <td>{{ $pemesanan->kamar->tipeKamar->nama_tipe_kamar ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pemesanan->check_in_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pemesanan->check_out_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $pemesanan->status == 'pending' ? 'bg-warning text-dark' : ($pemesanan->status == 'confirmed' ? 'bg-success' : ($pemesanan->status == 'checked_in' ? 'bg-primary' : ($pemesanan->status == 'checked_out' ? 'bg-info' : 'bg-secondary'))) }}">
                                            {{ ucfirst(str_replace('_', ' ', $pemesanan->status)) }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($pemesanan->total_harga, 2, ',', '.') }}</td>
                                    <td>
                                        @if ($pemesanan->fasilitas->isNotEmpty())
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($pemesanan->fasilitas as $fasilitas)
                                                    <li>- {{ $fasilitas->nama_fasilitas }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Tidak Ada
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-grid gap-1">
                                            <a href="{{ route('admin.pemesanans.show', $pemesanan->id_pemesanan) }}" class="btn btn-sm btn-info w-100 text-white" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada pemesanan yang tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
