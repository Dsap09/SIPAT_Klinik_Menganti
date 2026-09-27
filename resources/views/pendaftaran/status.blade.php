@extends('layouts.app')

@section('title', 'Status Pendaftaran')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm card-antrean">
                <div class="card-body p-4">
                    <p class="text-muted mb-1">Nomor Antrean Anda</p>
                    <p class="display-4 fw-bold text-primary mb-4">{{ $antrean->No_Antrean }}</p>

                    <dl class="row mb-0">
                        <dt class="col-5 col-sm-4">Nama</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->pasien->Nama_Lengkap }}</dd>

                        <dt class="col-5 col-sm-4">No RM</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->pasien->No_RM ?? 'Belum terbit (pasien baru)' }}</dd>

                        <dt class="col-5 col-sm-4">Poli</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->jadwal->poli->Nama_Poli }}</dd>

                        <dt class="col-5 col-sm-4">Dokter</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->jadwal->dokter->Nama_Dokter }}</dd>

                        <dt class="col-5 col-sm-4">Jadwal</dt>
                        <dd class="col-7 col-sm-8">
                            {{ $antrean->jadwal->Hari_Layanan }}, {{ substr($antrean->jadwal->Jam_Mulai, 0, 5) }}–{{ substr($antrean->jadwal->Jam_Selesai, 0, 5) }}
                        </dd>

                        <dt class="col-5 col-sm-4">Tanggal Kunjungan</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->Tanggal_Kunjungan->translatedFormat('d F Y') }}</dd>

                        <dt class="col-5 col-sm-4">Estimasi Waktu</dt>
                        <dd class="col-7 col-sm-8">
                            {{ $antrean->Estimasi_Waktu ? substr($antrean->Estimasi_Waktu, 0, 5) : 'Akan diinformasikan' }}
                        </dd>

                        <dt class="col-5 col-sm-4">Status</dt>
                        <dd class="col-7 col-sm-8">
                            <span class="badge text-bg-warning">{{ $antrean->Status }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="alert alert-info mt-3 mb-0">
                Tunjukkan halaman ini (nomor antrean, nama, dan tanggal lahir) kepada petugas saat check-in di klinik.
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('beranda') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
@endsection
