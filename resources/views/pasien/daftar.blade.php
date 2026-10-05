@extends('layouts.app')

@section('title', 'Pilih Jadwal Berobat')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <p class="section-eyebrow mb-1">Daftar berobat</p>
                <h1 class="h3 mb-1">Pilih Poli, Dokter &amp; Jadwal</h1>
                <p class="text-muted mb-0">
                    Data diri Anda sudah tersimpan — cukup pilih jadwal, lalu nomor antrean dan estimasi jam datang langsung terbit.
                </p>
            </div>

            <div class="info-tile mb-4">
                <span class="icon-tile mb-0"><x-si-icon name="user" class="sipat-icon-lg" /></span>
                <div>
                    <h2 class="h6 mb-1">{{ $pasien->Nama_Lengkap }} — {{ $pasien->No_RM }}</h2>
                    <p class="text-muted small mb-0">
                        {{ $pasien->Jenis_Pasien }} · {{ $pasien->Tgl_Lahir?->translatedFormat('d F Y') }} ·
                        {{ $pasien->No_Telepon }}
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2">
                    <x-si-icon name="alert" class="sipat-icon" />
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($pasien->Jenis_Pasien === 'BPJS')
                <div class="card">
                    <div class="card-body p-4 text-center">
                        <p class="mb-2 fw-semibold">Akun BPJS tidak dapat mendaftar antrean online.</p>
                        <p class="text-muted small mb-3">
                            Ambil nomor antrean melalui Mobile JKN, lalu tunjukkan ke petugas di loket untuk check-in.
                        </p>
                        <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-primary">Kembali ke Dashboard</a>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('pasien.daftar.simpan') }}" class="card">
                    @csrf

                    <div class="card-body p-4">
                        @forelse ($jadwal as $item)
                            <label class="select-card">
                                <input type="radio" name="ID_Jadwal" value="{{ $item->ID_Jadwal }}"
                                       @checked(old('ID_Jadwal') === $item->ID_Jadwal) required>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="jadwal-hari">{{ $item->Hari_Layanan }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">
                                            {{ $item->poli->Nama_Poli }} — {{ $item->dokter->Nama_Dokter }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ substr($item->Jam_Mulai, 0, 5) }}–{{ substr($item->Jam_Selesai, 0, 5) }}
                                            · sisa {{ $item->Sisa_Kuota }} kuota
                                        </div>
                                    </div>
                                    <span class="check-mark"><x-si-icon name="check-circle" /></span>
                                </div>
                            </label>
                        @empty
                            <div class="alert alert-warning mb-0">
                                Tidak ada jadwal dengan kuota tersedia saat ini. Silakan cek kembali nanti.
                            </div>
                        @endforelse

                        @error('ID_Jadwal')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card-footer bg-white border-top p-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100" @disabled($jadwal->isEmpty())>
                            Daftar &amp; Ambil Nomor Antrean
                        </button>
                    </div>
                </form>
            @endif

            <div class="text-center mt-3">
                <a href="{{ route('pasien.dashboard') }}" class="btn btn-link btn-sm">Kembali ke dashboard</a>
            </div>
        </div>
    </div>
@endsection
