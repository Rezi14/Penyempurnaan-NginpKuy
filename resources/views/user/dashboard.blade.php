{{-- resources/views/user/dashboard.blade.php --}}

@extends('layouts.user.app')

@section('title', 'Dashboard Pengguna')

@push('styles')
    <link href="{{ asset('css/dashboardpengguna.css') }}" rel="stylesheet">
@endpush

@section('content')

    {{-- UBAH: Gunakan spacing responsif (my-4 untuk mobile, my-md-5 untuk desktop) --}}
    <div class="container my-4 my-md-5 grow">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Intro Card --}}
        <div class="intro-card mb-4 mb-md-5 text-center">
            <div class="card-body">
                <h2 class="card-title fs-3 fs-md-2 mb-2">👋 Selamat Datang, {{ Auth::check() ? Auth::user()->name : 'Roomifers' }}!</h2>
                <p class="card-text fs-6 fs-md-5 opacity-75">Temukan kenyamanan dan kemewahan dalam pilihan kamar terbaik kami.</p>
            </div>
        </div>

        {{-- UBAH: Padding wrapper responsif (p-0 di mobile, p-4 di desktop) --}}
        <div class="p-0 p-md-4">
            <h2 class="section-title text-center mb-4 mb-md-5 fw-bold text-primary">Pilihan Kamar Tersedia</h2>

            @if ($kamarsTersedia->isEmpty())
                <div class="alert alert-warning text-center py-5 rounded-4 shadow-sm">
                    <p class="fs-5 fs-md-4 mb-0">Maaf, saat ini tidak ada kamar yang tersedia.</p>
                    <p class="mb-0 text-muted">Silakan coba lagi nanti.</p>
                </div>
            @else
                {{-- UBAH: Gutter (jarak antar kartu) responsif (g-3 mobile, g-4 desktop) --}}
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-4">
                    @foreach ($kamarsTersedia as $kamar)
                        <div class="col">
                            <div class="card h-100 room-card">
                                <img src="{{ asset($kamar->tipeKamar->foto_url) }}" class="card-img-top room-image"
                                    alt="Kamar {{ $kamar->nomor_kamar }}">

                                <div class="card-body d-flex flex-column">
                                    <span class="badge bg-primary mb-2 mb-md-3 align-self-start fs-6">
                                        {{ $kamar->tipeKamar->nama_tipe_kamar }}
                                    </span>

                                    <h3 class="card-title fs-4 fs-md-3 mb-1 fw-bold text-dark">
                                        Kamar No: {{ $kamar->nomor_kamar }}
                                    </h3>

                                    <p class="card-text mb-3 text-muted" style="font-size: 0.9rem;">
                                        {{ Str::limit($kamar->tipeKamar->deskripsi, 80) }}
                                    </p>

                                    @if ($kamar->tipeKamar->fasilitas->isNotEmpty())
                                        <div class="mb-3 p-2 p-md-3 border rounded-3 bg-white">
                                            <strong class="text-secondary d-block mb-1" style="font-size: 0.9rem;">Fasilitas:</strong>
                                            <ul class="list-unstyled fasilitas-list row g-1">
                                                @foreach ($kamar->tipeKamar->fasilitas as $fasilitas)
                                                    <li class="col-6">
                                                        <span class="fasilitas-icon">
                                                            @if ($fasilitas->nama_fasilitas == 'Wifi') 🌐
                                                            @elseif($fasilitas->nama_fasilitas == 'AC') ❄️
                                                            @elseif($fasilitas->nama_fasilitas == 'TV') 📺
                                                            @else ✨ @endif
                                                        </span>
                                                        {{ $fasilitas->nama_fasilitas }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="room-price-info mt-auto text-center text-md-end">
                                        <small class="text-muted d-block d-md-inline">Harga/Malam:</small>
                                        <span class="text-success fw-bold fs-5">
                                            Rp {{ number_format($kamar->tipeKamar->harga_per_malam, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="mt-3 text-center">
                                        @auth
                                            @if(Auth::user()->hasVerifiedEmail())
                                                <a href="{{ route('booking.create', ['kamar' => $kamar->id_kamar]) }}"
                                                    class="btn btn-success w-100 shadow-sm py-2">
                                                    Pesan Sekarang
                                                </a>
                                            @else
                                                <button class="btn btn-secondary w-100 shadow-sm mb-2" disabled>Verifikasi Email</button>
                                                <a href="{{ route('verification.notice') }}" class="btn btn-outline-warning w-100 btn-sm">Kirim Ulang</a>
                                            @endif
                                        @endauth
                                        @guest
                                            <a href="{{ route('login') }}" class="btn btn-primary w-100 shadow-sm">Login Pesan</a>
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
