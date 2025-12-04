<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul akan diisi oleh halaman anak --}}
    <title>@yield('title') - Roomify</title>

    <link rel="icon" href="{{ asset('img/Logo_B.png') }}">
    {{-- Memanggil file CSS yang baru saja kita buat --}}
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>

    {{--
      "Slot" ini akan diisi oleh container dan form dari
      halaman login.blade.php atau register.blade.php
    --}}
    @yield('content')
    @stack('scripts')
</body>

</html>
