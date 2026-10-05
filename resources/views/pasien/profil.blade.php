@extends('layouts.app')

@section('title', 'Profil Pasien')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <div>
                    <p class="section-eyebrow mb-1">Akun pasien</p>
                    <h1 class="h3 mb-0">Profil Saya</h1>
                </div>
                <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    Kembali ke Dashboard
                </a>
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

            <div class="card mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Data identitas</h2>
                    <p class="text-muted small">
                        Data identitas tidak dapat diubah sendiri. Hubungi petugas di loket bila ada kekeliruan.
                    </p>

                    <dl class="row mb-0 small">
                        <dt class="col-5 col-sm-4 text-muted fw-normal">Nomor RM</dt>
                        <dd class="col-7 col-sm-8 fw-semibold">{{ $pasien->No_RM }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Nama lengkap</dt>
                        <dd class="col-7 col-sm-8">{{ $pasien->Nama_Lengkap }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">NIK</dt>
                        <dd class="col-7 col-sm-8">{{ $pasien->NIK }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Tempat, tanggal lahir</dt>
                        <dd class="col-7 col-sm-8">
                            {{ $pasien->Tempat_Lahir }}, {{ $pasien->Tgl_Lahir?->translatedFormat('d F Y') }}
                        </dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Jenis kelamin</dt>
                        <dd class="col-7 col-sm-8">{{ $pasien->Jenis_Kelamin }}</dd>

                        <dt class="col-5 col-sm-4 text-muted fw-normal">Jenis pembayaran</dt>
                        <dd class="col-7 col-sm-8">
                            {{ $pasien->Jenis_Pasien }}
                            @if ($pasien->No_BPJS)
                                (No. BPJS {{ $pasien->No_BPJS }})
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <form method="POST" action="{{ route('pasien.profil.simpan') }}" class="card">
                @csrf
                @method('PUT')

                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Data dapat diperbarui</h2>
                    <p class="text-muted small">Perubahan data pasien tercatat pada audit trail klinik.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="No_Telepon" class="form-label">Nomor HP / WhatsApp</label>
                            <input type="tel" name="No_Telepon" id="No_Telepon"
                                   class="form-control @error('No_Telepon') is-invalid @enderror"
                                   value="{{ old('No_Telepon', $pasien->No_Telepon) }}" required>
                            @error('No_Telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="Penanggung_Jawab" class="form-label">Nama Penanggung Jawab</label>
                            <input type="text" name="Penanggung_Jawab" id="Penanggung_Jawab"
                                   class="form-control @error('Penanggung_Jawab') is-invalid @enderror"
                                   value="{{ old('Penanggung_Jawab', $pasien->Penanggung_Jawab) }}">
                            @error('Penanggung_Jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="Alamat" class="form-label">Alamat Lengkap</label>
                            <textarea name="Alamat" id="Alamat" rows="2"
                                      class="form-control @error('Alamat') is-invalid @enderror" required>{{ old('Alamat', $pasien->Alamat) }}</textarea>
                            @error('Alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="Agama" class="form-label">Agama</label>
                            <input type="text" name="Agama" id="Agama" class="form-control"
                                   value="{{ old('Agama', $pasien->Agama) }}">
                        </div>

                        <div class="col-md-4">
                            <label for="Pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" name="Pekerjaan" id="Pekerjaan" class="form-control"
                                   value="{{ old('Pekerjaan', $pasien->Pekerjaan) }}">
                        </div>

                        <div class="col-md-4">
                            <label for="Pendidikan" class="form-label">Pendidikan Terakhir</label>
                            <input type="text" name="Pendidikan" id="Pendidikan" class="form-control"
                                   value="{{ old('Pendidikan', $pasien->Pendidikan) }}">
                        </div>

                        <div class="col-md-4">
                            <label for="Status_Pernikahan" class="form-label">Status Pernikahan</label>
                            <select name="Status_Pernikahan" id="Status_Pernikahan" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach (['Belum menikah', 'Menikah', 'Cerai hidup', 'Cerai mati'] as $status)
                                    <option value="{{ $status }}" @selected(old('Status_Pernikahan', $pasien->Status_Pernikahan) === $status)>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4">
                    <button type="submit" class="btn btn-primary">
                        <x-si-icon name="check" class="sipat-icon-sm" /> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
