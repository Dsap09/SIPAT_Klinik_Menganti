@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('konten')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="queue-ticket mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-muted small mb-1">Nomor Rekam Medis Anda</p>
                            <p class="queue-number mb-1">{{ $pasien->No_RM }}</p>
                            <p class="mb-0 fw-semibold fs-5">{{ $pasien->Nama_Lengkap }}</p>
                        </div>
                        <span class="badge text-bg-{{ $pasien->Jenis_Pasien === 'BPJS' ? 'info' : 'secondary' }} fs-6 px-3 py-2">
                            {{ $pasien->Jenis_Pasien }}
                        </span>
                    </div>

                    <hr>

                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Nomor HP</dt>
                        <dd class="col-7">{{ $pasien->No_Telepon ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Tanggal lahir</dt>
                        <dd class="col-7">{{ $pasien->Tgl_Lahir?->translatedFormat('d F Y') ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Total kunjungan</dt>
                        <dd class="col-7">{{ $totalKunjungan }} kali ({{ $totalSelesai }} selesai)</dd>
                    </dl>
                </div>
            </div>

            <h2 class="h6 mb-2">Antrean hari ini</h2>

            @forelse ($antreanAktif as $antrean)
                @php
                    $warna = match ($antrean->Status) {
                        'Menunggu' => 'warning',
                        'Dilayani' => 'info',
                        'Selesai' => 'success',
                        default => 'secondary',
                    };
                @endphp
                <div class="card card-antrean mb-3 antrean-aktif"
                     data-url="{{ route('pendaftaran.status.data', $antrean->No_Antrean) }}">
                    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="queue-number mb-1">{{ $antrean->No_Antrean }}</p>
                            <p class="mb-0 small text-muted">
                                {{ $antrean->jadwal->poli->Nama_Poli }} — {{ $antrean->jadwal->dokter->Nama_Dokter }}
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge text-bg-{{ $warna }} fs-6 px-3 py-2 status-badge">{{ $antrean->Status }}</span>
                            <p class="mb-0 small text-muted mt-1">
                                Estimasi <span class="estimasi">
                                    {{ $antrean->Estimasi_Waktu ? substr($antrean->Estimasi_Waktu, 0, 5) : 'menyusul' }}
                                </span>
                            </p>
                        </div>
                        <a href="{{ route('pendaftaran.status', $antrean->No_Antrean) }}" class="btn btn-outline-primary btn-sm">
                            Lihat Status <x-si-icon name="chevron-right" class="sipat-icon-sm" />
                        </a>
                    </div>
                </div>
            @empty
                <div class="card mb-3">
                    <div class="card-body p-4 text-center">
                        <p class="text-muted mb-3">Belum ada antrean aktif untuk hari ini.</p>
                        @if ($pasien->Jenis_Pasien === 'BPJS')
                            <p class="small text-muted mb-0">
                                Pasien BPJS mengambil nomor antrean melalui Mobile JKN di loket, lalu petugas memverifikasi check-in.
                            </p>
                        @else
                            <a href="{{ route('pasien.daftar') }}" class="btn btn-primary">
                                <x-si-icon name="ticket" class="sipat-icon-sm" /> Daftar Berobat
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse

            @if ($pasien->Jenis_Pasien === 'BPJS')
                <div class="info-tile mb-3">
                    <span class="icon-tile mb-0"><x-si-icon name="info" class="sipat-icon-lg" /></span>
                    <div>
                        <h2 class="h6 mb-1">Pasien BPJS</h2>
                        <p class="text-muted small mb-0">
                            Pendaftaran antrean BPJS dilakukan melalui Mobile JKN dan dikonfirmasi petugas di loket.
                            Akun ini tetap bisa dipakai untuk melihat riwayat kunjungan.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="sticky-sidebar">
                <div class="card mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">Menu cepat</h2>
                        <div class="d-grid gap-2">
                            @if ($pasien->Jenis_Pasien !== 'BPJS')
                                <a href="{{ route('pasien.daftar') }}" class="btn btn-primary">
                                    <x-si-icon name="ticket" class="sipat-icon-sm" /> Daftar Berobat
                                </a>
                            @endif
                            <a href="{{ route('pasien.riwayat') }}" class="btn btn-outline-primary">
                                <x-si-icon name="clock" class="sipat-icon-sm" /> Riwayat Antrean
                            </a>
                            <a href="{{ route('pasien.profil') }}" class="btn btn-outline-primary">
                                <x-si-icon name="user" class="sipat-icon-sm" /> Profil Saya
                            </a>
                            <form method="POST" action="{{ route('pasien.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <x-si-icon name="lock" class="sipat-icon-sm" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="info-tile">
                    <span class="icon-tile mb-0"><x-si-icon name="shield" class="sipat-icon-lg" /></span>
                    <div>
                        <h2 class="h6 mb-1">Keamanan data</h2>
                        <p class="text-muted small mb-0">
                            Jangan bagikan nomor RM dan tanggal lahir Anda kepada orang lain. Semua akses akun
                            tercatat pada audit trail klinik.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('skrip')
    <script>
        (function () {
            var warna = { Menunggu: 'warning', Dilayani: 'info', Selesai: 'success', Batal: 'secondary' };
            var kartu = document.querySelectorAll('.antrean-aktif');

            function periksa() {
                kartu.forEach(function (item) {
                    fetch(item.dataset.url, { headers: { Accept: 'application/json' } })
                        .then(function (resp) { return resp.ok ? resp.json() : null; })
                        .then(function (data) {
                            if (!data) return;

                            var badge = item.querySelector('.status-badge');
                            badge.textContent = data.status;
                            badge.className = 'badge text-bg-' + (warna[data.status] || 'secondary')
                                + ' fs-6 px-3 py-2 status-badge';

                            var estimasi = item.querySelector('.estimasi');
                            if (estimasi) {
                                estimasi.textContent = data.estimasi_waktu || 'menyusul';
                            }
                        })
                        .catch(function () { /* coba lagi pada interval berikutnya */ });
                });
            }

            if (kartu.length > 0) {
                setInterval(periksa, 30000);
            }
        })();
    </script>
@endpush
