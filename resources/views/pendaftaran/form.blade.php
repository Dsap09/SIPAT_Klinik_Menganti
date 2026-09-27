@extends('layouts.app')

@section('title', 'Pendaftaran Online')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-1">Pendaftaran Online Pasien Umum</h1>
                    <p class="text-muted">Isi data berikut untuk mendapatkan nomor antrean.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pendaftaran.simpan') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="ID_Jadwal" class="form-label">Pilih Poli / Jadwal</label>
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

                        <fieldset class="mb-3">
                            <legend class="form-label fs-6">Jenis Pasien</legend>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="jenis-baru" value="baru" @checked(old('jenis', 'baru') === 'baru')>
                                <label class="form-check-label" for="jenis-baru">Pasien baru (belum punya No RM)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="jenis-lama" value="lama" @checked(old('jenis') === 'lama')>
                                <label class="form-check-label" for="jenis-lama">Pasien lama (punya No RM)</label>
                            </div>
                        </fieldset>

                        <div id="data-baru">
                            <div class="mb-3">
                                <label for="Nama_Lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" name="Nama_Lengkap" id="Nama_Lengkap" class="form-control @error('Nama_Lengkap') is-invalid @enderror" value="{{ old('Nama_Lengkap') }}">
                                @error('Nama_Lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="Tgl_Lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" name="Tgl_Lahir" id="Tgl_Lahir" class="form-control @error('Tgl_Lahir') is-invalid @enderror" value="{{ old('Tgl_Lahir') }}">
                                @error('Tgl_Lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="Alamat" class="form-label">Alamat</label>
                                <textarea name="Alamat" id="Alamat" rows="2" class="form-control @error('Alamat') is-invalid @enderror">{{ old('Alamat') }}</textarea>
                                @error('Alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="data-lama" class="d-none">
                            <div class="mb-3">
                                <label for="No_RM" class="form-label">No RM</label>
                                <input type="text" name="No_RM" id="No_RM" class="form-control @error('No_RM') is-invalid @enderror" value="{{ old('No_RM') }}" placeholder="Contoh: RM-000123">
                                @error('No_RM')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Daftar &amp; Ambil Nomor Antrean</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('skrip')
    <script>
        (function () {
            var baru = document.getElementById('jenis-baru');
            var lama = document.getElementById('jenis-lama');
            var dataBaru = document.getElementById('data-baru');
            var dataLama = document.getElementById('data-lama');

            function sync() {
                var isLama = lama.checked;
                dataLama.classList.toggle('d-none', !isLama);
                dataBaru.classList.toggle('d-none', isLama);
            }

            baru.addEventListener('change', sync);
            lama.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
