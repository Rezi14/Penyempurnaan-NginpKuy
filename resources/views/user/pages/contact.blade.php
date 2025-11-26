@extends('layouts.user.app')

@section('title', 'Kontak Kami - Roomify')

@section('content')
<div class="container py-5">

    {{-- Alert Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-5 align-items-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4">Hubungi Kami</h2>
                    <p class="mb-5 text-white-50">Butuh bantuan? Tim Roomify siap melayani Anda 24/7.</p>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0 btn-square bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-map-marker-alt fa-lg"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">Alamat</h5>
                            <p class="mb-0 text-white-50">Jl. Kaliurang Km 14.5, Yogyakarta</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0 btn-square bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">Email</h5>
                            <p class="mb-0 text-white-50">support@roomify.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-primary mb-4">Kirim Pesan</h3>

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Nama" value="{{ old('name') }}" required>
                                    <label for="name">Nama Lengkap</label>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required>
                                    <label for="email">Alamat Email</label>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" name="subject" id="subject" placeholder="Subjek" value="{{ old('subject') }}" required>
                                    <label for="subject">Subjek Pesan</label>
                                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control @error('message') is-invalid @enderror" name="message" placeholder="Pesan" id="message" style="height: 150px" required>{{ old('message') }}</textarea>
                                    <label for="message">Pesan Anda</label>
                                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" type="submit">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
