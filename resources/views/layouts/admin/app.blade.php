<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NginapKuy Admin')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admindashboard.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Tambahan CSS inline untuk memastikan layout flex berjalan jika admindashboard.css belum sempurna */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-x: hidden;
        }
    </style>
</head>
<body>

    {{-- 1. Navbar di paling atas --}}
    <x-admin.navbar />

    {{-- 2. Wrapper untuk Sidebar dan Konten --}}
    <div class="container-fluid p-0 admin-wrapper">

        {{-- Sidebar di kiri --}}
        <x-admin.sidebar />

        {{-- Konten Utama di kanan --}}
        <div class="main-content bg-light">
            @yield('content')
        </div>
    </div>

    {{-- 3. Footer --}}
    <x-admin.footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
