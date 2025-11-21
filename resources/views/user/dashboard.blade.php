{{-- 1. Gunakan layout master 'layouts.app' --}}
@extends('layouts.user.app')

{{-- 2. Set judul halaman ini --}}
@section('title', 'Dashboard Pengguna - NginapKuy')

@push('styles')
    <link href="{{ asset('css/dashboardpengguna.css') }}" rel="stylesheet">
@endpush

{{-- 3. Masukkan konten unik halaman ini ke slot 'content' --}}
@section('content')

    <div class="container my-5 grow">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-4 mb-4 text-center">
            <div class="card-body">
                <h2 class="card-title text-primary fs-3 fw-bold mb-3">Selamat Datang di Dasbor Anda!</h2>
                <p class="card-text text-muted fs-5">Temukan kamar yang tersedia dan kelola pengalaman pemesanan Anda dengan
                    mudah.</p>
            </div>
        </div>

        <div class="card p-4">
            <h2 class="section-title text-center mb-4">Pilihan Kamar Tersedia</h2>

            @if ($kamarsTersedia->isEmpty())
                <p class="text-center text-muted fs-5 py-4">Maaf, saat ini tidak ada kamar yang tersedia untuk ditampilkan.
                </p>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach ($kamarsTersedia as $kamar)
                        <div class="col">
                            <div class="card h-100">
                                <img src="{{ asset($kamar->tipeKamar->foto_url) }}" class="card-img-top room-image"
                                    alt="Kamar {{ $kamar->nomor_kamar }}">

                                <div class="card-body d-flex flex-column">
                                    <h3 class="card-title text-primary mb-3">Kamar Nomor: {{ $kamar->nomor_kamar }}</h3>
                                    <p class="card-text mb-2"><strong>Tipe Kamar:</strong>
                                        {{ $kamar->tipeKamar->nama_tipe_kamar }}</p>
                                    <p class="card-text mb-3 text-muted">{{ $kamar->tipeKamar->deskripsi }}</p>

                                    {{-- NEW: Tampilkan fasilitas default --}}
                                    @if($kamar->tipeKamar->fasilitas->isNotEmpty())
                                        <div class="mb-3">
                                            <strong>Fasilitas Termasuk:</strong>
                                            <ul class="list-unstyled">
                                                @foreach ($kamar->tipeKamar->fasilitas as $fasilitas)
                                                    <li>
                                                        {{ $fasilitas->nama_fasilitas }}
                                                        @if($fasilitas->biaya_tambahan > 0)
                                                            (Biaya: Rp {{ number_format($fasilitas->biaya_tambahan, 2, ',', '.') }})
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="text-muted">Tidak ada fasilitas tambahan yang termasuk.</p>
                                    @endif
                                    {{-- END NEW --}}

                                    <div class="room-price-info mt-auto">
                                        <strong>Harga/Malam:</strong> Rp
                                        {{ number_format($kamar->tipeKamar->harga_per_malam, 2, ',', '.') }}
                                    </div>

                                    <div class="mt-3 text-center">
                                        {{-- Cek apakah pengguna sudah login sebelum menampilkan tombol pesan --}}
                                        @if ($user)
                                            <a href="{{ route('booking.create', ['kamar' => $kamar->id_kamar]) }}"
                                                class="btn btn-success w-100">Pesan Sekarang</a>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-primary w-100">Login untuk
                                                Memesan</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection
