@extends('layouts.app')

@section('title', 'Beranda')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-2">Selamat datang di SIPAT</h1>
                    <p class="text-muted">
                        Sistem Antrian Online Terpadu Klinik Menganti. Daftar dari rumah untuk pasien umum,
                        dapatkan nomor antrean tanpa perlu datang lebih awal.
                    </p>
                    <a href="{{ route('pendaftaran.form') }}" class="btn btn-primary">Daftar Antrean Online</a>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="h6">Pendaftaran Online</h2>
                            <p class="text-muted small mb-0">
                                Pasien baru mengisi data diri; pasien lama cukup memasukkan No RM.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="h6">Antrian Terpadu</h2>
                            <p class="text-muted small mb-0">
                                Satu nomor urut yang adil untuk pasien umum dan BPJS.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
