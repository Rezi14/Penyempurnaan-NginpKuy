@extends('layouts.user.app')

@section('title', 'Profil Pengguna')

@section('content')
    <div class="container my-5">
        <h1 class="text-center">Halaman Profil Pengguna</h1>
        <p class="text-center">Ini adalah halaman profil Anda, {{ Auth::user()->name }}. Anda dapat melihat riwayat pemesanan di sini.</p>
        {{-- Logika untuk menampilkan riwayat pemesanan akan ditambahkan di sini --}}
    </div>
@endsection
