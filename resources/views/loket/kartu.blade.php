@extends('layouts.app')

@section('title', 'Kartu Antrian')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-4">
                    <h1 class="h6 text-muted mb-1">Klinik Menganti</h1>
                    <p class="mb-1">Kartu Antrian</p>
                    <p class="display-3 fw-bold text-primary mb-2">{{ $antrean->No_Antrean }}</p>
                    <p class="mb-0 fw-semibold">{{ $antrean->pasien->Nama_Lengkap }}</p>
                    <p class="text-muted small">
                        {{ $antrean->pasien->Jenis_Pasien }} — {{ $antrean->jadwal->poli->Nama_Poli }}
                    </p>

                    <hr>

                    <dl class="row mb-0 text-start small">
                        <dt class="col-5">Dokter</dt>
                        <dd class="col-7">{{ $antrean->jadwal->dokter->Nama_Dokter }}</dd>

                        <dt class="col-5">Jadwal</dt>
                        <dd class="col-7">
                            {{ $antrean->jadwal->Hari_Layanan }}, {{ substr($antrean->jadwal->Jam_Mulai, 0, 5) }}–{{ substr($antrean->jadwal->Jam_Selesai, 0, 5) }}
                        </dd>

                        <dt class="col-5">Tanggal</dt>
                        <dd class="col-7">{{ $antrean->Tanggal_Kunjungan->translatedFormat('d F Y') }}</dd>

                        <dt class="col-5">Waktu Check-in</dt>
                        <dd class="col-7">{{ $antrean->Waktu_CheckIn?->format('H:i') ?? '-' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3 no-print">
                <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Kartu</button>
                <a href="{{ url('/panel/antreans') }}" class="btn btn-outline-secondary">Kembali ke Antrean</a>
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
