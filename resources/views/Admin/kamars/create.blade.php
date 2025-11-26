@extends('layouts.admin.app')

@section('title', 'Tambah Kamar Baru - Roomify Admin')

@section('content')
    <div class="container-fluid p-4">
        {{-- Header Halaman --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Tambah Kamar Baru</h2>
            <a href="{{ route('admin.kamars.index') }}" class="btn bg-danger text-white">
                <i class="fas fa-arrow-left me-2 "></i> Kembali ke Daftar Kamar
            </a>
        </div>

        {{-- Card Form --}}
        <div class="card p-4 shadow-sm">
            <div class="card-body">
                {{-- Alert Error Validasi --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Tambah Kamar --}}
                <form action="{{ route('admin.kamars.store') }}" method="POST">
                    @csrf

                    {{-- Input Nomor Kamar --}}
                    <div class="mb-3">
                        <label for="nomor_kamar" class="form-label">Nomor Kamar</label>
                        <input type="text" class="form-control" id="nomor_kamar" name="nomor_kamar" value="{{ old('nomor_kamar') }}" required>
                    </div>

                    {{-- Select Tipe Kamar --}}
                    <div class="mb-3">
                        <label for="tipe_kamar_id" class="form-label">Tipe Kamar</label>
                        <select class="form-select" id="tipe_kamar_id" name="tipe_kamar_id" required>
                            <option value="">Pilih Tipe Kamar</option>
                            @foreach ($tipeKamars as $tipeKamar)
                                <option value="{{ $tipeKamar->id_tipe_kamar }}" {{ old('tipe_kamar_id') == $tipeKamar->id_tipe_kamar ? 'selected' : '' }}>
                                    {{ $tipeKamar->nama_tipe_kamar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Switch Status Kamar --}}
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status_kamar" name="status_kamar" value="1" {{ old('status_kamar', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_kamar">Status Kamar (Tersedia / Tidak Tersedia)</label>
                    </div>

                    {{-- Tombol Simpan --}}
                    <button type="submit" class="btn btn-primary">Simpan Kamar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
