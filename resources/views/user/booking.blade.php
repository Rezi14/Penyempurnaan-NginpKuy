<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kamar: {{ $kamar->nomor_kamar }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="{{ asset('css/booking.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container main-content">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="booking-card">
                    <div class="card-header-booking">Pesan Kamar: {{ $kamar->nomor_kamar }}</div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <img src="{{ asset($kamar->tipeKamar->foto_url) }}" class="room-image-detail" alt="Foto Kamar {{ $kamar->nomor_kamar }}">
                        </div>

                        <div class="room-details-section mb-4">
                            <h4>Detail Kamar</h4>
                            <p><strong>Nomor Kamar:</strong> {{ $kamar->nomor_kamar }}</p>
                            <p><strong>Tipe Kamar:</strong> {{ $kamar->tipeKamar->nama_tipe_kamar }}</p>
                            <p><strong>Harga Per Malam:</strong> Rp {{ number_format($kamar->tipeKamar->harga_per_malam, 2, ',', '.') }}</p>
                            <p><strong>Deskripsi Tipe:</strong> {{ $kamar->tipeKamar->deskripsi }}</p>
                            <p><strong>Status:</strong> {{ $kamar->status_kamar ? 'Tersedia' : 'Tidak Tersedia' }}</p>

                            {{-- NEW: Tampilkan fasilitas yang sudah termasuk dalam harga --}}
                            @if($kamar->tipeKamar->fasilitas->isNotEmpty())
                                <h5 class="mt-4">Fasilitas Termasuk:</h5>
                                <ul class="list-unstyled">
                                    @foreach ($kamar->tipeKamar->fasilitas as $fasilitas)
                                        <li>
                                            {{ $fasilitas->nama_fasilitas }}
                                            @if($fasilitas->biaya_tambahan > 0)
                                                (Biaya: Rp {{ number_format($fasilitas->biaya_tambahan, 2, ',', '.') }})
                                            @endif
                                        </li>
                                    @endforeach
                                    <li class="mt-2">
                                        <small class="text-muted">Biaya fasilitas ini sudah termasuk dalam perhitungan total harga pemesanan.</small>
                                    </li>
                                </ul>
                            @else
                                <p class="text-muted mt-4">Tidak ada fasilitas tambahan yang termasuk.</p>
                            @endif
                            {{-- END NEW --}}
                        </div>

                        <hr class="my-4">

                        <div class="booking-form-section">
                            <h4>Formulir Pemesanan</h4>
                            <form action="{{ route('booking.store') }}" method="POST">
                                @csrf

                                <input type="hidden" name="kamar_id" value="{{ $kamar->id_kamar }}">

                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Tanggal Check-in</label>
                                    <input type="date" class="form-control" id="check_in_date" name="check_in_date" value="{{ old('check_in_date') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Tanggal Check-out</label>
                                    <input type="date" class="form-control" id="check_out_date" name="check_out_date" value="{{ old('check_out_date') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jumlah_tamu" class="form-label">Jumlah Tamu</label>
                                    <input type="number" class="form-control" id="jumlah_tamu" name="jumlah_tamu" value="{{ old('jumlah_tamu', 1) }}" min="1" required>
                                </div>

                                {{-- PENGHAPUSAN: Bagian untuk memilih fasilitas telah dihapus --}}

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-submit-booking">Konfirmasi Pemesanan</button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-back-dashboard">Kembali ke Dashboard</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
