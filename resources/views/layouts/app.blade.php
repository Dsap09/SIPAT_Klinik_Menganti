<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Beranda') — SIPAT Klinik Menganti</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('beranda') }}">SIPAT</a>
            <span class="navbar-text text-white-50 d-none d-sm-inline">Klinik Menganti</span>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/panel') }}">Masuk Petugas</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="py-4 flex-grow-1">
        <div class="container">
            @if (session('sukses'))
                <div class="alert alert-success">{{ session('sukses') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('konten')
        </div>
    </main>

    <footer class="text-center text-muted small py-3">
        Sistem Antrian Online Terpadu — Klinik Menganti
    </footer>

    @stack('skrip')
</body>
</html>
