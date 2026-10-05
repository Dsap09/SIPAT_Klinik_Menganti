@extends('layouts.app')

@section('title', 'Masuk Akun Pasien')

@section('konten')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mb-4 text-center">
                <p class="section-eyebrow mb-1">Akun pasien</p>
                <h1 class="h3 mb-1">Masuk untuk Daftar Berobat</h1>
                <p class="text-muted mb-0">
                    Isi minimal 2 dari 3 data berikut: nomor RM, nomor HP, atau tanggal lahir.
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

            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pasien.login.proses') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="No_RM" class="form-label">Nomor RM</label>
                            <input type="text" name="No_RM" id="No_RM"
                                   class="form-control @error('No_RM') is-invalid @enderror"
                                   value="{{ old('No_RM') }}" placeholder="Contoh: RM-2026-0001" autofocus>
                            @error('No_RM')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <p class="text-muted small text-center mb-3">— atau —</p>

                        <div class="mb-3">
                            <label for="No_Telepon" class="form-label">Nomor HP</label>
                            <input type="tel" name="No_Telepon" id="No_Telepon"
                                   class="form-control @error('No_Telepon') is-invalid @enderror"
                                   value="{{ old('No_Telepon') }}" placeholder="08xxxxxxxxxx">
                            @error('No_Telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="Tgl_Lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" name="Tgl_Lahir" id="Tgl_Lahir"
                                   class="form-control @error('Tgl_Lahir') is-invalid @enderror"
                                   value="{{ old('Tgl_Lahir') }}" max="{{ today()->toDateString() }}">
                            @error('Tgl_Lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <x-si-icon name="lock" class="sipat-icon-sm" /> Masuk
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Lupa nomor RM?</h2>
                    <p class="text-muted small mb-2">
                        Masuk dengan nomor HP dan tanggal lahir yang terdaftar. Nomor RM Anda akan tampil di dashboard.
                    </p>
                    <p class="text-muted small mb-0">
                        Belum punya akun? <a href="{{ route('pasien.register') }}">Daftar akun baru</a> —
                        nomor RM diterbitkan otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
