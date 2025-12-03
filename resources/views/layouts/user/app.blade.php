<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul akan dinamis, diambil dari @section('title') --}}
    <title>@yield('title', 'Roomify')</title>

    {{-- CSS Bootstrap & Google Fonts --}}
    {{-- INI DIPERBAIKI: jstdelivr -> jsdelivr --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    {{-- CSS Kustom Anda --}}
    {{-- INI DIPERBAIKI: 'public/css/...' -> 'css/...' --}}
    @stack('styles')
</head>

<body>

    {{--
      (Pastikan komponen Anda ada di resources/views/components/user/navbar.blade.php)
    --}}
    <x-user.navbar :user="Auth::user()" />

    {{-- Konten utama halaman --}}
    <main class="container mb-5 grow">
        @yield('content')
    </main>

    {{--
      (Pastikan komponen Anda ada di resources/views/components/user/footer.blade.php)
    --}}
    <x-user.footer />

    {{-- Script JS Bootstrap --}}
    {{-- INI DIPERBAIKI: jstdelivr -> jsdelivr --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
