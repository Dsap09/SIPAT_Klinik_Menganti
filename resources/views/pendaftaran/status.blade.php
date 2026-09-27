@extends('layouts.app')

@section('title', 'Status Pendaftaran')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div id="notifikasi-status" class="alert alert-info d-none" role="status" aria-live="polite"></div>

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
                        <dd class="col-7 col-sm-8" id="estimasi-waktu">
                            {{ $antrean->Estimasi_Waktu ? substr($antrean->Estimasi_Waktu, 0, 5) : 'Akan diinformasikan' }}
                        </dd>

                        <dt class="col-5 col-sm-4">Status</dt>
                        <dd class="col-7 col-sm-8">
                            @php
                                $warna = match ($antrean->Status) {
                                    'Menunggu' => 'warning',
                                    'Dilayani' => 'info',
                                    'Selesai' => 'success',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge text-bg-{{ $warna }}" id="status-badge">{{ $antrean->Status }}</span>
                        </dd>
                    </dl>

                    <p class="text-muted small mt-3 mb-0">
                        Terakhir diperbarui <span id="diperbarui-pada">{{ now()->format('H:i:s') }}</span> · status diperiksa otomatis tiap 30 detik.
                    </p>
                </div>
            </div>

            @if ($antrean->bisaDibatalkan())
                <div class="d-flex flex-wrap gap-2 mt-3" id="aksi-pendaftaran">
                    <a href="{{ route('pendaftaran.jadwalUlang.form', $antrean->No_Antrean) }}" class="btn btn-outline-primary">
                        Jadwalkan Ulang
                    </a>
                    <form method="POST" action="{{ route('pendaftaran.batal', $antrean->No_Antrean) }}" onsubmit="return confirm('Batalkan pendaftaran ini? Kuota akan dikembalikan untuk pasien lain.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Batalkan Pendaftaran</button>
                    </form>
                </div>
            @endif

            <div class="alert alert-info mt-3 mb-0">
                Tunjukkan halaman ini (nomor antrean, nama, dan tanggal lahir) kepada petugas saat check-in di klinik.
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('beranda') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Beranda</a>
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
                badge.className = 'badge text-bg-' + (warna[data.status] || 'secondary');
                estimasi.textContent = data.estimasi_waktu || 'Akan diinformasikan';
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
                    .catch(function () { /* abaikan, coba lagi nanti */ });
            }

            setInterval(periksa, 30000);
        })();
    </script>
@endpush
