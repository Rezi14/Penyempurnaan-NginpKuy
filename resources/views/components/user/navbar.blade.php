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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid px-4">
            <a class="navbar-brand fs-4 fw-bold" href="#">Halo, {{ $user ? $user->name : 'Pengunjung' }}!</a>
            <div class="ms-auto">
                @if ($user)
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
    </nav>
</body>

</html>

{{-- <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('dashboard') }}">NginapKuy</a>
        <div class="ms-auto">
            <form action="{{ route('logout') }}" method="POST" class="d-flex">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
</nav> --}}



{{-- <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid px-4">
        <a class="navbar-brand fs-4 fw-bold" href="{{ route('dashboard') }}">NginapKuy</a>
        <div class="ms-auto">
            <form action="{{ route('logout') }}" method="POST" class="d-flex">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
</nav> --}}
