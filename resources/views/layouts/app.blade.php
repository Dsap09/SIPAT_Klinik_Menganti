<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f766e">
    <title>@yield('title', 'Beranda') — SIPAT {{ config('klinik.nama') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    @include('partials.nav')

    <main class="flex-grow-1">
        <div class="container py-4">
            @if (session('sukses'))
                <div class="alert alert-success d-flex align-items-start gap-2">
                    <x-si-icon name="check-circle" class="sipat-icon" />
                    <div>{{ session('sukses') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-start gap-2">
                    <x-si-icon name="alert" class="sipat-icon" />
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @yield('konten')
        </div>
    </main>

    @include('partials.footer')

    @stack('skrip')
</body>
</html>
