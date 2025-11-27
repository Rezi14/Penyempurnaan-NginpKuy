<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Pastikan CSS utama sudah dipanggil di Layout utama, baris ini opsional di komponen --}}
    {{-- <link href="{{ asset('css/dashboardpengguna.css') }}" rel="stylesheet"> --}}
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid px-4">
            {{-- Brand / Sapaan User --}}
            <a class="navbar-brand fs-4 fw-bold" href="{{ route('dashboard') }}">
                Halo, {{ Auth::check() ? Auth::user()->name : 'Roomifers' }}!
            </a>

            {{-- Tombol Toggler (Hamburger) untuk Mobile --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Konten Navbar --}}
            <div class="collapse navbar-collapse" id="navbarContent">

                {{-- Daftar Menu (Kiri) --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">

                    {{-- 1. Menu Home --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}"
                           aria-current="page"
                           href="{{ route('dashboard') }}">Home</a>
                    </li>

                    {{-- 2. Menu Contact --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active fw-bold' : '' }}"
                           href="{{ route('contact') }}">Contact</a>
                    </li>

                    {{-- 3. Menu Profil --}}
                    {{-- Hanya tampilkan menu Profil jika user sudah Login (Opsional, tapi disarankan) --}}
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('profile') ? 'active fw-bold' : '' }}"
                               href="{{ route('profile') }}">Profil</a>
                        </li>
                    @endauth
                </ul>

                {{-- Tombol Login/Logout (Kanan) --}}
                <div class="d-flex mt-3 mt-lg-0">
                    @if (Auth::check())
                        <form action="{{ route('logout') }}" method="POST" class="d-flex">
                            @csrf
                            <button type="submit" class="btn btn-danger">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-light me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-success">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>
</body>
</html>
