<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('img/Logo_B.png') }}" type="image/png">
    <link href="{{ asset('css/admindashboard.css') }}?v=3" rel="stylesheet">

    <style>
        /* Tambahan inline untuk memastikan layout responsif */
        * {
            box-sizing: border-box;
        }

        @media (max-width: 991px) {
            .container-fluid.grow {
                display: block !important;
            }
        }
    </style>
</head>

<body>

    {{-- Navbar (Fixed Top) --}}
    <x-admin.navbar />

    {{-- Container untuk Sidebar dan Main Content --}}
    <div class="container-fluid grow d-flex">
        {{-- Sidebar (Fixed Left on Desktop, Overlay on Mobile) --}}
        <x-admin.sidebar />

        {{-- Main Content --}}
        <main class="main-content">
            @yield('content')

            {{-- Footer dalam main content agar ikut terdorong --}}
            <x-admin.footer />
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;

            if (toggleBtn && sidebar) {
                // Toggle sidebar ketika tombol diklik
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                    body.classList.toggle('sidebar-open');
                });

                // Tutup sidebar jika klik di luar sidebar (area gelap)
                document.addEventListener('click', function(e) {
                    if (body.classList.contains('sidebar-open') &&
                        !sidebar.contains(e.target) &&
                        !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove('show');
                        body.classList.remove('sidebar-open');
                    }
                });

                // Tutup sidebar ketika link navigasi diklik (pada mobile)
                const navLinks = sidebar.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 991) {
                            sidebar.classList.remove('show');
                            body.classList.remove('sidebar-open');
                        }
                    });
                });

                // Handle resize window
                let resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        if (window.innerWidth > 991) {
                            // Pada desktop, pastikan sidebar tidak memiliki class 'show'
                            sidebar.classList.remove('show');
                            body.classList.remove('sidebar-open');
                        }
                    }, 250);
                });
            }

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>

</html>
