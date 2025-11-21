<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="{{ asset('css/dashboardpengguna.css') }}" rel="stylesheet">
</head>

<body>
    {{-- Navbar Baru dengan Navigasi Tambahan --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid px-4">
            <a class="navbar-brand fs-4 fw-bold" href="{{ route('dashboard') }}">NginapKuy</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        {{-- Menggunakan rute placeholder 'profile' yang sudah ada di group middleware 'auth' --}}
                        <a class="nav-link text-white" href="{{ route('profile') }}">Profil</a>
                    </li>
                    <li class="nav-item">
                        {{-- Menggunakan rute placeholder 'contact' yang baru dibuat --}}
                        <a class="nav-link text-white" href="{{ route('contact') }}">Kontak</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    @if ($user)
                        <span class="navbar-text me-3 text-white d-none d-lg-block">
                            Halo, {{ $user->name }}!
                        </span>
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
    {{-- Akhir Navbar Baru --}}
</body>

</html>
