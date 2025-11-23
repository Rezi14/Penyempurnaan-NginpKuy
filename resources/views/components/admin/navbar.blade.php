<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Panel - NginapKuy</a>
        <div class="ms-auto">
            @if (Auth::check())
                <form action="{{ route('logout') }}" method="POST" class="d-flex">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            @endif
        </div>
    </div>
</nav>
