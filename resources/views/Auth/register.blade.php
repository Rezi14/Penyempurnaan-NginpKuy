{{-- 1. Gunakan layout "guest" yang sama --}}
@extends('layouts.guest')

{{-- 2. Set judul halaman --}}
@section('title', 'Daftar Akun Baru')

{{-- 3. Masukkan konten ke "slot" @yield('content') --}}
@section('content')
{{-- Gunakan class .auth-container yang sama --}}
<div class="auth-container">
    <h2>Daftar Akun Baru</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ganti url() dengan route() agar konsisten --}}
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        {{-- Tambahkan class .btn-register agar tombolnya berwarna hijau --}}
        <button type="submit" class="btn-primary btn-register">Daftar</button>

        <div class="text-center">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </form>
</div>
@endsection
