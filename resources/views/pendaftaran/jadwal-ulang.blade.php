@extends('layouts.app')

@section('title', 'Jadwalkan Ulang')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <p class="section-eyebrow mb-1">Jadwal ulang</p>
                <h1 class="h3 mb-1">Jadwalkan Ulang Pendaftaran</h1>
                <p class="text-muted mb-0">
                    Nomor antrean <strong>{{ $antrean->No_Antrean }}</strong> akan dibatalkan dan kuotanya
                    dikembalikan, lalu Anda menerima nomor baru pada jadwal yang dipilih.
                </p>
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

            <div class="info-tile mb-3">
                <span class="icon-tile mb-0"><x-si-icon name="ticket" class="sipat-icon-lg" /></span>
                <div class="small">
                    <div class="fw-semibold">Antrean saat ini</div>
                    <div class="text-muted">
                        {{ $antrean->jadwal->poli->Nama_Poli }} — {{ $antrean->jadwal->dokter->Nama_Dokter }}<br>
                        {{ $antrean->jadwal->Hari_Layanan }},
                        {{ substr($antrean->jadwal->Jam_Mulai, 0, 5) }}–{{ substr($antrean->jadwal->Jam_Selesai, 0, 5) }}
                        · status {{ $antrean->Status }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('pendaftaran.jadwalUlang.simpan', $antrean->No_Antrean) }}" class="card">
                @csrf

                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Pilih jadwal baru</h2>
                    <p class="text-muted small">Hanya jadwal dengan kuota tersedia yang dapat dipilih.</p>

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
                            Tidak ada jadwal dengan kuota tersedia saat ini.
                        </div>
                    @endforelse

                    @error('ID_Jadwal')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card-footer bg-white border-top p-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary" @disabled($jadwal->isEmpty())>
                        <x-si-icon name="refresh" class="sipat-icon-sm" /> Jadwalkan Ulang
                    </button>
                    <a href="{{ route('pendaftaran.status', $antrean->No_Antrean) }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
