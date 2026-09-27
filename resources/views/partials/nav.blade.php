<nav class="navbar navbar-expand-lg sipat-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('beranda') }}">
            <span class="brand-mark"><x-si-icon name="pulse" /></span>
            <span class="d-flex flex-column lh-1">
                <span class="fw-bold">SIPAT</span>
                <small class="brand-sub">{{ config('klinik.nama') }}</small>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama"
                aria-controls="navUtama" aria-expanded="false" aria-label="Buka menu navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navUtama">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#layanan">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#jadwal">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-sm btn-outline-primary" href="{{ url('/panel') }}">
                        <x-si-icon name="lock" class="sipat-icon-sm" /> Masuk Petugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-primary" href="{{ route('pendaftaran.form') }}">Daftar Antrean</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
