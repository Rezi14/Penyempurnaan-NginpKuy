{{-- 1. Gunakan layout master 'layouts.user.app' --}}
@extends('layouts.user.app')

{{-- 2. Set judul halaman ini --}}
@section('title', 'Dashboard Pengguna')

@push('styles')
    <link href="{{ asset('css/dashboardpengguna.css') }}" rel="stylesheet">
@endpush

{{-- 3. Masukkan konten unik halaman ini ke slot 'content' --}}
@section('content')

    <div class="container my-5 grow">
        {{-- Alert Sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Kartu Sambutan --}}
        <div class="intro-card mb-5 text-center">
            <div class="card-body">
                <h2 class="card-title fs-2 mb-2">👋 Selamat Datang, {{ Auth::check() ? Auth::user()->name : 'Pengunjung' }}!
                </h2>
                <p class="card-text fs-5 opacity-75">Temukan kenyamanan dan kemewahan dalam pilihan kamar terbaik kami.</p>
            </div>
        </div>

        <div class="p-4">
            <h2 class="section-title text-center mb-5 fw-bold text-primary">Pilihan Kamar Tersedia</h2>

            @if ($kamarsTersedia->isEmpty())
                <div class="alert alert-warning text-center py-5 rounded-4 shadow-sm">
                    <p class="fs-4 mb-0">Maaf, saat ini tidak ada kamar yang tersedia untuk ditampilkan.</p>
                    <p class="mb-0 text-muted">Silakan coba lagi nanti.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach ($kamarsTersedia as $kamar)
                        <div class="col">
                            {{-- Kartu Kamar --}}
                            <div class="card h-100 room-card">
                                <img src="{{ asset($kamar->tipeKamar->foto_url) }}" class="card-img-top room-image"
                                    alt="Kamar {{ $kamar->nomor_kamar }}">

                                <div class="card-body d-flex flex-column">
                                    <span
                                        class="badge bg-primary mb-3 align-self-start fs-6">{{ $kamar->tipeKamar->nama_tipe_kamar }}</span>

                                    <h3 class="card-title mb-1 fw-bold text-dark">Kamar Nomor: {{ $kamar->nomor_kamar }}
                                    </h3>
                                    <p class="card-text mb-3 text-muted" style="font-size: 0.95rem;">
                                        {{ Str::limit($kamar->tipeKamar->deskripsi, 80) }}
                                    </p>

                                    {{-- Tampilkan fasilitas default dengan ikon --}}
                                    @if ($kamar->tipeKamar->fasilitas->isNotEmpty())
                                        <div class="mb-3 p-3 border rounded-3 bg-white">
                                            <strong class="text-secondary d-block mb-1">Fasilitas Termasuk:</strong>
                                            <ul class="list-unstyled fasilitas-list row g-1">
                                                @foreach ($kamar->tipeKamar->fasilitas as $fasilitas)
                                                    <li class="col-6">
                                                        <span class="fasilitas-icon">
                                                            @if ($fasilitas->nama_fasilitas == 'Wifi')
                                                                🌐
                                                            @elseif($fasilitas->nama_fasilitas == 'AC')
                                                                ❄️
                                                            @elseif($fasilitas->nama_fasilitas == 'TV')
                                                                📺
                                                            @elseif($fasilitas->nama_fasilitas == 'Bathtub')
                                                                🛁
                                                            @elseif($fasilitas->nama_fasilitas == 'Balkon')
                                                                🏞️
                                                            @elseif($fasilitas->nama_fasilitas == 'Ruang Keluarga')
                                                                🛋️
                                                            @else
                                                                ✨
                                                            @endif
                                                        </span>
                                                        {{ $fasilitas->nama_fasilitas }}
                                                        @if ($fasilitas->biaya_tambahan > 0)
                                                            <small class="text-danger"> (+Rp
                                                                {{ number_format($fasilitas->biaya_tambahan, 0, ',', '.') }})</small>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    <div class="room-price-info mt-auto text-center">
                                        Harga/Malam:
                                        <span class="text-success ms-2">Rp
                                            {{ number_format($kamar->tipeKamar->harga_per_malam, 2, ',', '.') }}</span>
                                    </div>

                                    <div class="mt-3 text-center">
                                        @auth
                                            {{-- User Login: Cek Verifikasi Email --}}
                                            @if(Auth::user()->hasVerifiedEmail())
                                                {{-- Jika SUDAH verifikasi, tombol aktif --}}
                                                <a href="{{ route('booking.create', ['kamar' => $kamar->id_kamar]) }}"
                                                    class="btn btn-success w-100 btn-lg shadow-sm">
                                                    Pesan Sekarang
                                                </a>
                                            @else
                                                {{-- Jika BELUM verifikasi, tombol non-aktif + info --}}
                                                <button type="button" class="btn btn-secondary w-100 btn-lg shadow-sm mb-2" disabled>
                                                    Verifikasi Email Dulu
                                                </button>
                                                <a href="{{ route('verification.notice') }}" class="btn btn-outline-warning w-100 btn-sm">
                                                    Kirim Ulang Verifikasi
                                                </a>
                                            @endif
                                        @endauth

                                        @guest
                                            {{-- User Tamu: Login dulu --}}
                                            <a href="{{ route('login') }}"
                                                class="btn btn-primary w-100 btn-lg shadow-sm">Login untuk Memesan</a>
                                        @endguest
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
