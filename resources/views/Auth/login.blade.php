{{-- 1. Gunakan layout "guest" yang baru --}}
@extends('layouts.guest')

{{-- 2. Set judul halaman --}}
@section('title', 'Login')

{{-- 3. Masukkan konten ke "slot" @yield('content') --}}
@section('content')
<div class="auth-container">
    <h2>Login</h2>

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

    {{-- Saya ganti url('/login') menjadi route('login') agar lebih standar --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email_or_name">Email atau Nama Pengguna</label>
            <input type="text" id="email_or_name" name="email_or_name" value="{{ old('email_or_name') }}" required autofocus>
            @error('email_or_name')
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
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Ingat Saya</label>
        </div>

        <button type="submit" class="btn-primary">Login</button>

        <div class="text-center">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </div>
    </form>
</div>
@endsection
