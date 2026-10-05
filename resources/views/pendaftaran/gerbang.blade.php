@extends('layouts.app')

@section('title', 'Pendaftaran Online')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <p class="section-eyebrow mb-1">Pendaftaran online</p>
                <h1 class="h3 mb-2">Satu Akun untuk Berobat &amp; Riwayat Kunjungan</h1>
                <p class="text-muted mb-0">
                    Mulai sekarang pendaftaran antrean online menggunakan akun pasien. Dengan akun ini Anda
                    mendapatkan nomor rekam medis (RM) otomatis, tidak perlu mengisi data berulang setiap berobat.
                </p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="info-tile h-100">
                        <span class="icon-tile mb-0"><x-si-icon name="user" class="sipat-icon-lg" /></span>
                        <div>
                            <h2 class="h6 mb-1">Pasien baru</h2>
                            <p class="text-muted small mb-0">Daftar akun sekali, langsung dapat nomor RM.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-tile h-100">
                        <span class="icon-tile mb-0"><x-si-icon name="lock" class="sipat-icon-lg" /></span>
                        <div>
                            <h2 class="h6 mb-1">Pasien lama</h2>
                            <p class="text-muted small mb-0">Masuk dengan No RM/no. HP dan tanggal lahir.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-tile h-100">
                        <span class="icon-tile mb-0"><x-si-icon name="calendar" class="sipat-icon-lg" /></span>
                        <div>
                            <h2 class="h6 mb-1">Daftar berobat</h2>
                            <p class="text-muted small mb-0">Pilih poli &amp; jadwal, dapat nomor antrean.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4 text-center">
                    <h2 class="h6 mb-3">Sudah punya akun?</h2>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <a href="{{ route('pasien.login') }}" class="btn btn-primary btn-lg">
                            <x-si-icon name="lock" class="sipat-icon-sm" /> Masuk Akun Pasien
                        </a>
                        <a href="{{ route('pasien.register') }}" class="btn btn-outline-primary btn-lg">
                            <x-si-icon name="user" class="sipat-icon-sm" /> Daftar Akun Baru
                        </a>
                    </div>

                    <p class="text-muted small mb-0">
                        Belum pernah berobat dan ingin datang langsung? Silakan menuju loket pendaftaran,
                        petugas akan membantu memasukkan data Anda.
                    </p>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('beranda') }}" class="btn btn-link btn-sm">Cek status antrean tanpa akun di beranda</a>
            </div>
        </div>
    </div>
@endsection
