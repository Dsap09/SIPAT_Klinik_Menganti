@extends('layouts.app')

@section('title', 'Kartu Antrian')

@section('konten')
    <div class="row justify-content-center kartu-cetak-baris">
        <div class="col-md-7 col-lg-6 kartu-cetak-kolom">
            <div class="queue-ticket">
                <div class="card-body p-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <span class="brand-mark"><x-si-icon name="pulse" /></span>
                        <div class="lh-1 text-start">
                            <div class="fw-bold">{{ config('klinik.nama') }}</div>
                            <small class="text-muted">Kartu Antrian</small>
                        </div>
                    </div>

                    <p class="queue-number mb-2">{{ $antrean->No_Antrean }}</p>

                    <p class="mb-0 fw-semibold fs-5">{{ $antrean->pasien->Nama_Lengkap }}</p>
                    <p class="text-muted small mb-4">
                        {{ $antrean->pasien->Jenis_Pasien }} — {{ $antrean->jadwal->poli->Nama_Poli }}
                    </p>

                    <dl class="row mb-0 text-start small">
                        <dt class="col-5 text-muted fw-normal">No RM</dt>
                        <dd class="col-7 fw-semibold">{{ $antrean->pasien->No_RM ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Dokter</dt>
                        <dd class="col-7 fw-semibold">{{ $antrean->jadwal->dokter->Nama_Dokter }}</dd>

                        <dt class="col-5 text-muted fw-normal">Jadwal</dt>
                        <dd class="col-7">
                            {{ $antrean->jadwal->Hari_Layanan }},
                            {{ substr($antrean->jadwal->Jam_Mulai, 0, 5) }}–{{ substr($antrean->jadwal->Jam_Selesai, 0, 5) }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Tanggal</dt>
                        <dd class="col-7">{{ $antrean->Tanggal_Kunjungan->translatedFormat('d F Y') }}</dd>

                        <dt class="col-5 text-muted fw-normal">Waktu check-in</dt>
                        <dd class="col-7">{{ $antrean->Waktu_CheckIn?->format('H:i') ?? '-' }}</dd>
                    </dl>

                    <hr>

                    <p class="text-muted small mb-0">
                        Mohon menunggu hingga nomor Anda dipanggil. Terima kasih atas kesabaran Anda.
                    </p>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 no-print d-print-none">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <x-si-icon name="ticket" class="sipat-icon-sm" /> Cetak Kartu
                </button>
                <a href="{{ url('/panel/antreans') }}" class="btn btn-outline-secondary">Kembali ke Antrean</a>
                <p class="text-muted small mb-0 w-100">
                    Di dialog cetak, pilih ukuran kertas sesuai printer: 58/80&nbsp;mm untuk printer thermal,
                    atau A4/Letter bila memakai printer biasa. Kartu otomatis mengisi lebar kertas tersebut.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('skrip')
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
@endpush
