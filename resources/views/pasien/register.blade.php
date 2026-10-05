@extends('layouts.app')

@section('title', 'Daftar Akun Pasien')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <p class="section-eyebrow mb-1">Akun pasien</p>
                <h1 class="h3 mb-1">Pendaftaran Akun &amp; Nomor Rekam Medis</h1>
                <p class="text-muted mb-0">
                    Isi data diri sesuai KTP. Setelah tersimpan, Anda langsung mendapatkan nomor rekam medis (RM)
                    dan dapat mendaftar berobat tanpa mengisi data ulang.
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

            <form method="POST" action="{{ route('pasien.register.simpan') }}">
                @csrf

                <div class="card mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-1">1. Data wajib</h2>
                        <p class="text-muted small">Data inti untuk penerbitan nomor rekam medis.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Nama_Lengkap" class="form-label">Nama Lengkap (sesuai KTP)</label>
                                <input type="text" name="Nama_Lengkap" id="Nama_Lengkap"
                                       class="form-control @error('Nama_Lengkap') is-invalid @enderror"
                                       value="{{ old('Nama_Lengkap') }}" autocomplete="name" required>
                                @error('Nama_Lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="NIK" class="form-label">NIK (sesuai KTP)</label>
                                <input type="text" name="NIK" id="NIK"
                                       class="form-control @error('NIK') is-invalid @enderror"
                                       value="{{ old('NIK') }}" inputmode="numeric" maxlength="16" required>
                                @error('NIK')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Tempat_Lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" name="Tempat_Lahir" id="Tempat_Lahir"
                                       class="form-control @error('Tempat_Lahir') is-invalid @enderror"
                                       value="{{ old('Tempat_Lahir') }}" required>
                                @error('Tempat_Lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Tgl_Lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" name="Tgl_Lahir" id="Tgl_Lahir"
                                       class="form-control @error('Tgl_Lahir') is-invalid @enderror"
                                       value="{{ old('Tgl_Lahir') }}" max="{{ today()->toDateString() }}" required>
                                @error('Tgl_Lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Jenis_Kelamin" class="form-label">Jenis Kelamin</label>
                                <select name="Jenis_Kelamin" id="Jenis_Kelamin"
                                        class="form-select @error('Jenis_Kelamin') is-invalid @enderror" required>
                                    <option value="">— Pilih —</option>
                                    @foreach (\App\Models\Pasien::JENIS_KELAMIN as $jenisKelamin)
                                        <option value="{{ $jenisKelamin }}" @selected(old('Jenis_Kelamin') === $jenisKelamin)>
                                            {{ $jenisKelamin }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('Jenis_Kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="No_Telepon" class="form-label">Nomor HP / WhatsApp</label>
                                <input type="tel" name="No_Telepon" id="No_Telepon"
                                       class="form-control @error('No_Telepon') is-invalid @enderror"
                                       value="{{ old('No_Telepon') }}" placeholder="08xxxxxxxxxx" required>
                                @error('No_Telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Dipakai untuk masuk akun dan verifikasi data.</div>
                            </div>

                            <div class="col-12">
                                <label for="Alamat" class="form-label">Alamat Lengkap (hingga kelurahan/kecamatan)</label>
                                <textarea name="Alamat" id="Alamat" rows="2"
                                          class="form-control @error('Alamat') is-invalid @enderror" required>{{ old('Alamat') }}</textarea>
                                @error('Alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Jenis_Pasien" class="form-label">Jenis Pembayaran</label>
                                <select name="Jenis_Pasien" id="Jenis_Pasien"
                                        class="form-select @error('Jenis_Pasien') is-invalid @enderror" required>
                                    <option value="UMUM" @selected(old('Jenis_Pasien', 'UMUM') === 'UMUM')>Umum</option>
                                    <option value="BPJS" @selected(old('Jenis_Pasien') === 'BPJS')>BPJS</option>
                                </select>
                                @error('Jenis_Pasien')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="kolom-no-bpjs">
                                <label for="No_BPJS" class="form-label">Nomor BPJS</label>
                                <input type="text" name="No_BPJS" id="No_BPJS"
                                       class="form-control @error('No_BPJS') is-invalid @enderror"
                                       value="{{ old('No_BPJS') }}" inputmode="numeric">
                                @error('No_BPJS')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pendaftaran antrean BPJS tetap melalui Mobile JKN di loket.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-1">2. Data disarankan</h2>
                        <p class="text-muted small">Membantu petugas saat pelayanan dan kondisi darurat.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="Agama" class="form-label">Agama</label>
                                <input type="text" name="Agama" id="Agama" class="form-control"
                                       value="{{ old('Agama') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="Pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" name="Pekerjaan" id="Pekerjaan" class="form-control"
                                       value="{{ old('Pekerjaan') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="Penanggung_Jawab" class="form-label">Nama Penanggung Jawab</label>
                                <input type="text" name="Penanggung_Jawab" id="Penanggung_Jawab" class="form-control"
                                       value="{{ old('Penanggung_Jawab') }}">
                                <div class="form-text">Untuk pasien di bawah umur atau kondisi darurat.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-1">3. Data opsional</h2>
                        <p class="text-muted small">Boleh dikosongkan.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Status_Pernikahan" class="form-label">Status Pernikahan</label>
                                <select name="Status_Pernikahan" id="Status_Pernikahan" class="form-select">
                                    <option value="">— Pilih —</option>
                                    @foreach (['Belum menikah', 'Menikah', 'Cerai hidup', 'Cerai mati'] as $status)
                                        <option value="{{ $status }}" @selected(old('Status_Pernikahan') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="Pendidikan" class="form-label">Pendidikan Terakhir</label>
                                <input type="text" name="Pendidikan" id="Pendidikan" class="form-control"
                                       value="{{ old('Pendidikan') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer bg-white p-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Daftar &amp; Dapatkan Nomor RM
                        </button>
                        <p class="text-center text-muted small mt-2 mb-0">
                            Sudah punya akun? <a href="{{ route('pasien.login') }}">Masuk di sini</a>.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('skrip')
    <script>
        (function () {
            var jenis = document.getElementById('Jenis_Pasien');
            var kolom = document.getElementById('kolom-no-bpjs');
            var bpjs = document.getElementById('No_BPJS');

            function sync() {
                var isBpjs = jenis.value === 'BPJS';
                kolom.classList.toggle('d-none', !isBpjs);
                bpjs.required = isBpjs;
            }

            jenis.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
