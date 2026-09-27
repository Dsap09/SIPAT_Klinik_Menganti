@extends('layouts.app')

@section('title', 'Beranda')

@section('konten')
    <section class="hero">
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-7">
                <span class="hero-eyebrow">
                    <x-si-icon name="pulse" class="sipat-icon-sm" /> Antrean online {{ config('klinik.nama') }}
                </span>
                <h1 class="mt-3 mb-2">{{ config('klinik.tagline') }}</h1>
                <p class="hero-lead mb-4">
                    Pasien umum dapat mendaftar dari rumah dan datang mendekati jam pelayanan.
                    Pasien BPJS tetap terlayani pada <strong>satu nomor urut yang sama</strong> — tanpa ada yang tertinggal.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-lg" href="{{ route('pendaftaran.form') }}">
                        Daftar Antrean Online <x-si-icon name="arrow-right" class="sipat-icon-sm" />
                    </a>
                    <a class="btn btn-outline-light btn-lg" href="#jadwal">Lihat Jadwal Layanan</a>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <span class="hero-stat"><x-si-icon name="ticket" class="sipat-icon-sm" /> Satu nomor urut BPJS &amp; Umum</span>
                    <span class="hero-stat"><x-si-icon name="clock" class="sipat-icon-sm" /> Estimasi jam datang</span>
                    <span class="hero-stat"><x-si-icon name="shield" class="sipat-icon-sm" /> Data pasien dilindungi</span>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="hero-panel">
                    <h2 class="h6 mb-3">Cek status antrean</h2>
                    <form method="GET" action="{{ route('pendaftaran.cek') }}" class="d-flex gap-2">
                        <input type="text" name="no" class="form-control" placeholder="Contoh: A-001"
                               aria-label="Nomor antrean" required>
                        <button class="btn btn-primary px-3" type="submit" aria-label="Cek status">
                            <x-si-icon name="search" class="sipat-icon-sm" />
                        </button>
                    </form>
                    <p class="small text-white-50 mb-0 mt-2">
                        Masukkan nomor antrean untuk melihat status terbaru, estimasi waktu, serta opsi
                        membatalkan atau menjadwalkan ulang.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="cara-daftar">
        <div class="section-head">
            <p class="section-eyebrow mb-1">Cara daftar</p>
            <h2 class="h3 mb-1">Empat langkah, tanpa menunggu di loket</h2>
            <p class="text-muted mb-0">
                Seluruh proses pendaftaran pasien umum dapat diselesaikan dari ponsel sebelum datang ke klinik.
            </p>
        </div>

        <div class="row g-3">
            @foreach ([
                ['judul' => 'Pilih poli & jadwal', 'teks' => 'Tentukan poli, dokter, dan jam layanan yang masih memiliki kuota.'],
                ['judul' => 'Isi data pasien', 'teks' => 'Pasien baru mengisi nama, tanggal lahir, dan alamat. Pasien lama cukup memasukkan No RM.'],
                ['judul' => 'Dapat nomor antrean', 'teks' => 'Sistem menerbitkan nomor antrean beserta estimasi jam datang Anda.'],
                ['judul' => 'Check-in di klinik', 'teks' => 'Petugas mencocokkan nama dan tanggal lahir, lalu kartu antrian dicetak.'],
            ] as $langkah)
                <div class="col-md-6 col-xl-3">
                    <div class="step-card card-hover">
                        <span class="step-number">{{ $loop->iteration }}</span>
                        <h3 class="h6 mb-1">{{ $langkah['judul'] }}</h3>
                        <p class="text-muted small mb-0">{{ $langkah['teks'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section" id="layanan">
        <div class="section-head">
            <p class="section-eyebrow mb-1">Layanan</p>
            <h2 class="h3 mb-1">Poli yang tersedia</h2>
            <p class="text-muted mb-0">
                Daftar poli berikut dikelola langsung oleh admin klinik, lengkap dengan dokter yang bertugas.
            </p>
        </div>

        <div class="row g-3">
            @forelse ($poli as $item)
                <div class="col-md-6 col-xl-3">
                    <div class="poli-card card-hover">
                        <span class="icon-tile"><x-si-icon name="building" class="sipat-icon-lg" /></span>
                        <h3 class="h6 mb-1">{{ $item->Nama_Poli }}</h3>
                        <p class="text-muted small mb-2">{{ $item->Deskripsi ?: 'Melayani pasien umum dan BPJS.' }}</p>
                        <span class="badge-soft">
                            <x-si-icon name="users" class="sipat-icon-sm" /> {{ $item->dokter_count }} dokter
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        Data poli belum tersedia. Silakan hubungi petugas klinik.
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <section class="section" id="jadwal">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div class="section-head mb-0">
                <p class="section-eyebrow mb-1">Jadwal</p>
                <h2 class="h3 mb-1">Jadwal layanan dengan kuota tersedia</h2>
                <p class="text-muted mb-0">Kuota berkurang setiap ada pendaftaran, jadi sebaiknya daftar lebih awal.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('pendaftaran.form') }}">Daftar Sekarang</a>
        </div>

        @forelse ($jadwal as $item)
            <div class="jadwal-item card-hover">
                <span class="jadwal-hari">{{ $item->Hari_Layanan }}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $item->poli->Nama_Poli }}</div>
                    <div class="text-muted small">{{ $item->dokter->Nama_Dokter }}</div>
                </div>
                <div class="text-lg-end">
                    <div class="fw-semibold">{{ substr($item->Jam_Mulai, 0, 5) }}–{{ substr($item->Jam_Selesai, 0, 5) }}</div>
                    <span class="badge-soft">{{ $item->Sisa_Kuota }} kuota tersisa</span>
                </div>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('pendaftaran.form') }}">Daftar</a>
            </div>
        @empty
            <div class="alert alert-info mb-0">
                Belum ada jadwal dengan kuota tersedia saat ini. Silakan cek kembali nanti atau hubungi petugas klinik.
            </div>
        @endforelse
    </section>

    <section class="section" id="bpjs">
        <div class="section-head">
            <p class="section-eyebrow mb-1">Alur pasien</p>
            <h2 class="h3 mb-1">Untuk pasien BPJS dan pasien umum</h2>
            <p class="text-muted mb-0">
                Keduanya memakai satu nomor urut yang sama sehingga urutan antrean tetap adil.
            </p>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="info-tile h-100">
                    <span class="icon-tile mb-0"><x-si-icon name="shield" class="sipat-icon-lg" /></span>
                    <div>
                        <h3 class="h6 mb-1">Pasien BPJS</h3>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Daftar melalui aplikasi <strong>Mobile JKN</strong> dan dapatkan nomor dari BPJS.</li>
                            <li>Petugas memasukkan data tersebut ke SIPAT pada hari kunjungan.</li>
                            <li>Check-in di loket dengan menunjukkan nomor antrean, nama, dan tanggal lahir.</li>
                            <li>Kartu antrian dicetak dan Anda menunggu dipanggil.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="info-tile h-100">
                    <span class="icon-tile mb-0"><x-si-icon name="user" class="sipat-icon-lg" /></span>
                    <div>
                        <h3 class="h6 mb-1">Pasien umum</h3>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Daftar online dari rumah melalui halaman ini.</li>
                            <li>Pasien baru mengisi data diri; pasien lama cukup memasukkan No RM.</li>
                            <li>Datang mendekati estimasi jam yang tertera, lalu check-in di loket.</li>
                            <li>Belum sempat daftar online? Datang langsung dan petugas akan membantu.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="faq">
        <div class="section-head">
            <p class="section-eyebrow mb-1">FAQ</p>
            <h2 class="h3 mb-1">Pertanyaan yang sering diajukan</h2>
        </div>

        <div class="accordion" id="faqSipat">
            @foreach ([
                ['t' => 'Apa itu No RM dan bagaimana saya mendapatkannya?', 'j' => 'No RM adalah nomor rekam medis pasien. Pasien yang sudah pernah berobat dapat memakai No RM saat mendaftar. Pasien baru cukup mengisi data diri dan nomor rekam medis akan diterbitkan petugas saat kunjungan pertama.'],
                ['t' => 'Bagaimana jika kuota jadwal yang saya tuju habis?', 'j' => 'Jadwal dengan kuota habis tidak dapat dipilih saat pendaftaran. Silakan pilih jadwal atau poli lain yang masih tersedia, atau hubungi petugas klinik untuk bantuan.'],
                ['t' => 'Bisakah saya membatalkan atau menjadwalkan ulang?', 'j' => 'Bisa, selama antrean belum di-check-in di loket. Buka halaman status antrean Anda, lalu pilih Batalkan Pendaftaran atau Jadwalkan Ulang. Kuota akan otomatis dikembalikan agar dapat dipakai pasien lain.'],
                ['t' => 'Saya pasien BPJS, apakah bisa daftar online di sini?', 'j' => 'Pendaftaran online pada halaman ini untuk pasien umum. Pasien BPJS mendaftar melalui Mobile JKN, lalu petugas memasukkan nomor tersebut ke SIPAT sehingga urutan antrean tetap satu dan adil.'],
                ['t' => 'Apakah data pribadi saya aman?', 'j' => 'Setiap perubahan data pasien dan tindakan pada antrean tercatat pada audit trail, akses dibatasi sesuai peran pengguna, dan basis data dibackup otomatis setiap hari sesuai ketentuan perlindungan data rekam medis.'],
            ] as $faq)
                <div class="accordion-item">
                    <h3 class="accordion-header" id="faqHeading{{ $loop->iteration }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faqPanel{{ $loop->iteration }}" aria-expanded="false"
                                aria-controls="faqPanel{{ $loop->iteration }}">
                            {{ $faq['t'] }}
                        </button>
                    </h3>
                    <div id="faqPanel{{ $loop->iteration }}" class="accordion-collapse collapse"
                         aria-labelledby="faqHeading{{ $loop->iteration }}" data-bs-parent="#faqSipat">
                        <div class="accordion-body text-muted small">{{ $faq['j'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section pb-0">
        <div class="card border-0">
            <div class="card-body p-4 p-lg-5 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="h4 mb-1">Siap mendaftar?</h2>
                    <p class="text-muted mb-0">
                        Butuh kurang dari satu menit. Nomor antrean dan estimasi jam datang langsung terbit.
                    </p>
                </div>
                <a class="btn btn-primary btn-lg" href="{{ route('pendaftaran.form') }}">
                    Daftar Antrean Online <x-si-icon name="arrow-right" class="sipat-icon-sm" />
                </a>
            </div>
        </div>
    </section>
@endsection
