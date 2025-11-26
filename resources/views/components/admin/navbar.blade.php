<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid px-4">
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-3 d-lg-none text-white" id="sidebarToggle" href="#">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Roomify</a>

        <div class="ms-auto">
            @if (Auth::check())
                <form action="{{ route('logout') }}" method="POST" class="d-flex">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            @endif
        </div>
    </div>
</nav>
