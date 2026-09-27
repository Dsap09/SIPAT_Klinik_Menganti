@extends('layouts.app')

@section('title', 'Jadwalkan Ulang')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-1">Jadwalkan Ulang Pendaftaran</h1>
                    <p class="text-muted">
                        Nomor antrean <strong>{{ $antrean->No_Antrean }}</strong> akan dibatalkan dan kuotanya dikembalikan,
                        lalu Anda mendapat nomor baru pada jadwal yang dipilih.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pendaftaran.jadwalUlang.simpan', $antrean->No_Antrean) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="ID_Jadwal" class="form-label">Pilih Poli / Jadwal Baru</label>
                            <select name="ID_Jadwal" id="ID_Jadwal" class="form-select @error('ID_Jadwal') is-invalid @enderror" required>
                                <option value="">— Pilih jadwal —</option>
                                @foreach ($jadwal as $item)
                                    <option value="{{ $item->ID_Jadwal }}" @selected(old('ID_Jadwal') === $item->ID_Jadwal)>
                                        {{ $item->poli->Nama_Poli }} — {{ $item->dokter->Nama_Dokter }} — {{ $item->Hari_Layanan }}, {{ substr($item->Jam_Mulai, 0, 5) }}–{{ substr($item->Jam_Selesai, 0, 5) }} (sisa {{ $item->Sisa_Kuota }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ID_Jadwal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($jadwal->isEmpty())
                                <div class="form-text text-danger">Tidak ada jadwal dengan kuota tersedia saat ini.</div>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Jadwalkan Ulang</button>
                            <a href="{{ route('pendaftaran.status', $antrean->No_Antrean) }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
