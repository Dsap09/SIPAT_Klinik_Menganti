@extends('layouts.app')

@section('title', 'Pendaftaran Online')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4">
                <p class="section-eyebrow mb-1">Pendaftaran pasien umum</p>
                <h1 class="h3 mb-1">Pendaftaran Online Pasien Umum</h1>
                <p class="text-muted mb-0">
                    Isi data berikut untuk mendapatkan nomor antrean dan estimasi jam datang.
                    Tidak perlu membuat akun.
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

            <div class="row g-4">
                <div class="col-lg-7">
                    <form method="POST" action="{{ route('pendaftaran.simpan') }}" class="card">
                        @csrf

                        <div class="card-body p-4">
                            <h2 class="h6 mb-1">1. Pilih poli &amp; jadwal</h2>
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
                                    Tidak ada jadwal dengan kuota tersedia saat ini. Silakan cek kembali nanti.
                                </div>
                            @endforelse

                            @error('ID_Jadwal')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                            <hr class="my-4">

                            <h2 class="h6 mb-1">2. Data pasien</h2>
                            <p class="text-muted small">Pasien lama cukup memasukkan No RM, data lain tidak perlu diisi.</p>

                            <div class="btn-group w-100 mb-4" role="group" aria-label="Jenis pasien">
                                <input type="radio" class="btn-check" name="jenis" id="jenis-baru" value="baru"
                                       @checked(old('jenis', 'baru') === 'baru')>
                                <label class="btn btn-outline-primary" for="jenis-baru">Pasien baru</label>

                                <input type="radio" class="btn-check" name="jenis" id="jenis-lama" value="lama"
                                       @checked(old('jenis') === 'lama')>
                                <label class="btn btn-outline-primary" for="jenis-lama">Pasien lama (punya No RM)</label>
                            </div>

                            <div id="data-baru">
                                <div class="mb-3">
                                    <label for="Nama_Lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" name="Nama_Lengkap" id="Nama_Lengkap"
                                           class="form-control @error('Nama_Lengkap') is-invalid @enderror"
                                           value="{{ old('Nama_Lengkap') }}" autocomplete="name">
                                    @error('Nama_Lengkap')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="Tgl_Lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="Tgl_Lahir" id="Tgl_Lahir"
                                           class="form-control @error('Tgl_Lahir') is-invalid @enderror"
                                           value="{{ old('Tgl_Lahir') }}">
                                    @error('Tgl_Lahir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Dipakai petugas untuk verifikasi saat check-in.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="Alamat" class="form-label">Alamat</label>
                                    <textarea name="Alamat" id="Alamat" rows="2"
                                              class="form-control @error('Alamat') is-invalid @enderror">{{ old('Alamat') }}</textarea>
                                    @error('Alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="data-lama" class="d-none">
                                <div class="mb-3">
                                    <label for="No_RM" class="form-label">No RM</label>
                                    <input type="text" name="No_RM" id="No_RM"
                                           class="form-control @error('No_RM') is-invalid @enderror"
                                           value="{{ old('No_RM') }}" placeholder="Contoh: RM-000123"
                                           inputmode="text">
                                    @error('No_RM')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">No RM tertera pada kartu berobat atau hasil kunjungan sebelumnya.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top p-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100"
                                    @disabled($jadwal->isEmpty())>
                                Daftar &amp; Ambil Nomor Antrean
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="sticky-sidebar">
                        <div class="info-tile mb-3">
                            <span class="icon-tile mb-0"><x-si-icon name="ticket" class="sipat-icon-lg" /></span>
                            <div>
                                <h2 class="h6 mb-1">Setelah mendaftar</h2>
                                <p class="text-muted small mb-0">
                                    Nomor antrean dan estimasi jam datang tampil di halaman status. Simpan halaman itu
                                    untuk ditunjukkan kepada petugas saat check-in.
                                </p>
                            </div>
                        </div>

                        <div class="info-tile mb-3">
                            <span class="icon-tile mb-0"><x-si-icon name="clock" class="sipat-icon-lg" /></span>
                            <div>
                                <h2 class="h6 mb-1">Estimasi jam datang</h2>
                                <p class="text-muted small mb-0">
                                    Dihitung dari jam mulai layanan ditambah waktu layanan rata-rata per pasien.
                                    Datang mendekati jam tersebut agar tidak menunggu lama.
                                </p>
                            </div>
                        </div>

                        <div class="info-tile mb-3">
                            <span class="icon-tile mb-0"><x-si-icon name="info" class="sipat-icon-lg" /></span>
                            <div>
                                <h2 class="h6 mb-1">Bisa dibatalkan</h2>
                                <p class="text-muted small mb-0">
                                    Belum sempat datang? Batalkan atau jadwalkan ulang dari halaman status
                                    selama belum check-in. Kuota otomatis dikembalikan.
                                </p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <h2 class="h6 mb-3">Jam layanan</h2>
                                <ul class="list-unstyled small mb-0">
                                    @foreach (config('klinik.jam') as $baris)
                                        <li class="d-flex justify-content-between gap-3 py-1">
                                            <span class="text-muted">{{ $baris['hari'] }}</span>
                                            <span class="fw-semibold">{{ $baris['jam'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
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
