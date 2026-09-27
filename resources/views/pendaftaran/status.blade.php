@extends('layouts.app')

@section('title', 'Status Pendaftaran')

@section('konten')
    @php
        $urutanStatus = ['Menunggu' => 1, 'Dilayani' => 2, 'Selesai' => 3];
        $posisiStatus = $urutanStatus[$antrean->Status] ?? 0;
        $warnaStatus = match ($antrean->Status) {
            'Menunggu' => 'warning',
            'Dilayani' => 'info',
            'Selesai' => 'success',
            default => 'secondary',
        };
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div id="notifikasi-status" class="alert alert-info d-none d-flex align-items-start gap-2" role="status" aria-live="polite"></div>

            <div class="queue-ticket mb-3">
                <div class="card-body p-4 p-lg-5 text-center">
                    <p class="text-muted small mb-1">Nomor antrean Anda</p>
                    <p class="queue-number mb-3">{{ $antrean->No_Antrean }}</p>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <span class="badge text-bg-{{ $warnaStatus }} fs-6 px-3 py-2" id="status-badge">
                            {{ $antrean->Status }}
                        </span>
                        <span class="badge-soft fs-6 px-3 py-2">
                            <x-si-icon name="clock" class="sipat-icon-sm" />
                            Estimasi <span id="estimasi-waktu">{{ $antrean->Estimasi_Waktu ? substr($antrean->Estimasi_Waktu, 0, 5) : 'menyusul' }}</span>
                        </span>
                    </div>

                    @if ($posisiStatus > 0)
                        <div class="status-steps mb-2" aria-hidden="true">
                            @foreach (['Menunggu', 'Dilayani', 'Selesai'] as $label)
                                @php
                                    $angka = $urutanStatus[$label];
                                    $kelas = $angka < $posisiStatus ? 'is-done' : ($angka === $posisiStatus ? 'is-current' : '');
                                @endphp
                                <div class="status-step {{ $kelas }}">{{ $label }}</div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-secondary py-2 mb-2">
                            Pendaftaran ini sudah dibatalkan. Kuota telah dikembalikan untuk pasien lain.
                        </div>
                    @endif

                    <p class="text-muted small mb-0">
                        Diperbarui otomatis setiap 30 detik · terakhir <span id="diperbarui-pada">{{ now()->format('H:i:s') }}</span>
                    </p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Rincian pendaftaran</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 col-sm-4 text-muted fw-normal">Nama</dt>
                        <dd class="col-7 col-sm-8 fw-semibold">{{ $antrean->pasien->Nama_Lengkap }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">No RM</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->pasien->No_RM ?? 'Belum terbit (pasien baru)' }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Poli</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->jadwal->poli->Nama_Poli }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Dokter</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->jadwal->dokter->Nama_Dokter }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Jadwal</dt>
                        <dd class="col-7 col-sm-8">
                            {{ $antrean->jadwal->Hari_Layanan }},
                            {{ substr($antrean->jadwal->Jam_Mulai, 0, 5) }}–{{ substr($antrean->jadwal->Jam_Selesai, 0, 5) }}
                        </dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Tanggal kunjungan</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->Tanggal_Kunjungan->translatedFormat('d F Y') }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Waktu check-in</dt>
                        <dd class="col-7 col-sm-8">{{ $antrean->Waktu_CheckIn?->format('H:i') ?? 'Belum check-in' }}</dd>
                    </dl>
                </div>
            </div>

            @if ($antrean->bisaDibatalkan())
                <div class="d-flex flex-wrap gap-2 mb-3 no-print" id="aksi-pendaftaran">
                    <a href="{{ route('pendaftaran.jadwalUlang.form', $antrean->No_Antrean) }}" class="btn btn-outline-primary">
                        <x-si-icon name="refresh" class="sipat-icon-sm" /> Jadwalkan Ulang
                    </a>
                    <form method="POST" action="{{ route('pendaftaran.batal', $antrean->No_Antrean) }}"
                          onsubmit="return confirm('Batalkan pendaftaran ini? Kuota akan dikembalikan untuk pasien lain.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <x-si-icon name="alert" class="sipat-icon-sm" /> Batalkan Pendaftaran
                        </button>
                    </form>
                </div>
            @endif

            <div class="info-tile">
                <span class="icon-tile mb-0"><x-si-icon name="info" class="sipat-icon-lg" /></span>
                <div>
                    <h2 class="h6 mb-1">Saat tiba di klinik</h2>
                    <p class="text-muted small mb-0">
                        Tunjukkan halaman ini (nomor antrean, nama, dan tanggal lahir) kepada petugas.
                        Petugas akan memverifikasi data Anda, lalu kartu antrian dicetak.
                    </p>
                </div>
            </div>

            <div class="text-center mt-3 no-print">
                <a href="{{ route('beranda') }}" class="btn btn-link btn-sm">Kembali ke beranda</a>
            </div>
        </div>
    </div>
@endsection

@push('skrip')
    <script>
        (function () {
            var url = @json(route('pendaftaran.status.data', $antrean->No_Antrean));
            var badge = document.getElementById('status-badge');
            var estimasi = document.getElementById('estimasi-waktu');
            var diperbarui = document.getElementById('diperbarui-pada');
            var notifikasi = document.getElementById('notifikasi-status');
            var aksi = document.getElementById('aksi-pendaftaran');
            var warna = { Menunggu: 'warning', Dilayani: 'info', Selesai: 'success', Batal: 'secondary' };
            var statusTerakhir = badge.textContent.trim();

            function terapkan(data) {
                badge.textContent = data.status;
                badge.className = 'badge text-bg-' + (warna[data.status] || 'secondary') + ' fs-6 px-3 py-2';
                estimasi.textContent = data.estimasi_waktu || 'menyusul';
                diperbarui.textContent = data.diperbarui_pada;

                if (data.status !== statusTerakhir) {
                    statusTerakhir = data.status;
                    notifikasi.textContent = 'Status antrean Anda berubah: ' + data.pesan;
                    notifikasi.classList.remove('d-none');

                    if (aksi) {
                        aksi.classList.add('d-none');
                    }
                }
            }

            function periksa() {
                fetch(url, { headers: { Accept: 'application/json' } })
                    .then(function (resp) { return resp.ok ? resp.json() : null; })
                    .then(function (data) { if (data) terapkan(data); })
                    .catch(function () { /* coba lagi pada interval berikutnya */ });
            }

            setInterval(periksa, 30000);
        })();
    </script>
@endpush
