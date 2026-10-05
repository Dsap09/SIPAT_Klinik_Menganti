@extends('layouts.app')

@section('title', 'Riwayat Antrean')

@section('konten')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <p class="section-eyebrow mb-1">Akun pasien</p>
            <h1 class="h3 mb-0">Riwayat Antrean</h1>
        </div>
        <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if ($riwayat->isEmpty())
                <div class="p-4 text-center text-muted">
                    Belum ada riwayat kunjungan. Riwayat akan tampil setelah Anda mendaftar berobat.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">No</th>
                                <th scope="col">Poli</th>
                                <th scope="col">Dokter</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $item)
                                @php
                                    $warna = match ($item->Status) {
                                        'Menunggu' => 'warning',
                                        'Dilayani' => 'info',
                                        'Selesai' => 'success',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="small">{{ $item->Tanggal_Kunjungan->translatedFormat('d M Y') }}</td>
                                    <td class="fw-semibold">{{ $item->No_Antrean }}</td>
                                    <td class="small">{{ $item->jadwal->poli->Nama_Poli }}</td>
                                    <td class="small">{{ $item->jadwal->dokter->Nama_Dokter }}</td>
                                    <td><span class="badge text-bg-{{ $warna }}">{{ $item->Status }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('pendaftaran.status', $item->No_Antrean) }}" class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($riwayat->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>
@endsection
