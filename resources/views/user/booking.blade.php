@extends('layouts.user.app')

{{-- 2. Set judul halaman ini --}}
@section('title', 'Pemesanan')

@push('styles')
    <link href="{{ asset('css/booking.css') }}" rel="stylesheet">
@endpush

{{-- 3. Masukkan konten unik halaman ini ke slot 'content' --}}
@section('content')
    <div class="container main-content">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="booking-card">
                    <div class="card-header-booking">Pesan Kamar: {{ $kamar->nomor_kamar }}</div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="text-center mb-4">
                            <img src="{{ asset($kamar->tipeKamar->foto_url) }}" class="room-image-detail"
                                alt="Foto Kamar {{ $kamar->nomor_kamar }}">
                        </div>

                        <div class="room-details-section mb-4">
                            <h4>Detail Kamar</h4>
                            <p><strong>Nomor Kamar:</strong> {{ $kamar->nomor_kamar }}</p>
                            <p><strong>Tipe Kamar:</strong> {{ $kamar->tipeKamar->nama_tipe_kamar }}</p>
                            <p><strong>Harga Per Malam:</strong> Rp
                                {{ number_format($kamar->tipeKamar->harga_per_malam, 2, ',', '.') }}</p>
                            <p><strong>Deskripsi Tipe:</strong> {{ $kamar->tipeKamar->deskripsi }}</p>
                            <p><strong>Status:</strong> {{ $kamar->status_kamar ? 'Tersedia' : 'Tidak Tersedia' }}</p>

                            @if ($kamar->tipeKamar->fasilitas->isNotEmpty())
                                <h5 class="mt-4">Fasilitas Termasuk (Gratis):</h5>
                                <ul class="list-unstyled">
                                    @foreach ($kamar->tipeKamar->fasilitas as $fasilitas)
                                        <li><i class="bi bi-check-circle-fill text-success"></i>
                                            {{ $fasilitas->nama_fasilitas }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <hr class="my-4">

                        <div class="booking-form-section">
                            <h4>Formulir Pemesanan</h4>
                            <form action="{{ route('booking.store') }}" method="POST">
                                @csrf

                                <input type="hidden" name="kamar_id" value="{{ $kamar->id_kamar }}">

                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Tanggal Check-in</label>
                                    <input type="date" class="form-control" id="check_in_date" name="check_in_date"
                                        value="{{ old('check_in_date') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Tanggal Check-out</label>
                                    <input type="date" class="form-control" id="check_out_date" name="check_out_date"
                                        value="{{ old('check_out_date') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jumlah_tamu" class="form-label">Jumlah Tamu</label>
                                    <input type="number" class="form-control" id="jumlah_tamu" name="jumlah_tamu"
                                        value="{{ old('jumlah_tamu', 1) }}" min="1" required>
                                </div>

                                {{-- BAGIAN FASILITAS TAMBAHAN DITAMBAHKAN KEMBALI --}}
                                @if ($fasilitasTersedia->isNotEmpty())
                                    <div class="mt-4 mb-3">
                                        <h5 class="mb-3 fs-6 fw-bold">Fasilitas Tambahan (Opsional)</h5>
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                @foreach ($fasilitasTersedia as $fasilitas)
                                                    <div
                                                        class="form-check d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <input class="form-check-input" type="checkbox"
                                                                name="fasilitas_ids[]"
                                                                value="{{ $fasilitas->id_fasilitas }}"
                                                                id="fasilitas_{{ $fasilitas->id_fasilitas }}">
                                                            <label class="form-check-label"
                                                                for="fasilitas_{{ $fasilitas->id_fasilitas }}">
                                                                {{ $fasilitas->nama_fasilitas }}
                                                            </label>
                                                        </div>
                                                        <span class="text-primary fw-bold small">
                                                            + Rp
                                                            {{ number_format($fasilitas->biaya_tambahan, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                {{-- AKHIR BAGIAN FASILITAS --}}

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-submit-booking">Konfirmasi Pemesanan</button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-back-dashboard">Kembali ke
                                        Dashboard</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
