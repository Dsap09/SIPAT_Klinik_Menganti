# SIPAT — Sistem Antrian Online Terpadu Klinik Menganti

Aplikasi antrian klinik berbasis Laravel: pendaftaran online pasien umum dengan akun pasien, antrian terpadu BPJS + umum dalam satu nomor urut, check-in + cetak kartu, dan dashboard monitoring.

Dokumen perencanaan dan rencana sprint ada di `docs/` (mulai dari `docs/PLAN.md`). Usulan pasca-MVP (belum diimplementasikan): `docs/RENCANA_BRIDGING_PCARE.md`.

## Kebutuhan
- PHP >= 8.3 (diuji pada 8.4) dengan ekstensi `zip` aktif (dibutuhkan Filament)
- Composer
- MySQL 8
- Node.js + npm

## Menjalankan
1. `composer install`
2. `npm install`
3. Salin `.env.example` menjadi `.env`, sesuaikan kredensial DB, lalu `php artisan key:generate`
4. Buat database `sipat_klinik_menganti`, lalu `php artisan migrate --seed`
5. `npm run build` (atau `npm run dev` saat pengembangan)
6. `php artisan serve`

- **Halaman pasien** (Bootstrap): `/`, `/pasien/register`, `/pasien/login`, `/pasien/daftar`, `/pasien/dashboard`, `/status/{noAntrean}`
- **Panel staf** (Filament): `/panel` — akun seeder `admin`, `petugas`, `manajemen`, `dokter` (password `password`)

### Akun pasien
- Pendaftaran mandiri di `/pasien/register` otomatis menerbitkan **No RM** (`RM-{TAHUN}-{URUTAN}`).
- Login tanpa password di `/pasien/login`: cukup benar **2 dari 3** data (No RM / No telepon / Tanggal lahir).
- Booking online (`/pasien/daftar`) hanya untuk pasien **UMUM** dan wajib punya akun; pasien BPJS tetap lewat loket (petugas input dari Mobile JKN).

Profil klinik (alamat, telepon, jam layanan, peta) diatur di `config/klinik.php`. Nilainya masih **contoh** —
ganti dengan data resmi klinik lalu ubah `'contoh' => false` agar penanda di footer hilang.

Jika tampilan panel tidak ter-style, jalankan `php artisan filament:assets`.

## Pengujian
```bash
php artisan test
vendor/bin/pint
```

## Status
- **Semua sprint (1-5) selesai**: pendaftaran online pasien umum, antrian tunggal (A-001), estimasi waktu, auth + RBAC + audit trail, input BPJS/walk-in, check-in + kartu antrian, panel Filament (master data + dashboard + panduan pengguna), batal/jadwal ulang, notifikasi status, backup harian.
- **Pasca-MVP**: akun pasien — No RM otomatis, login 2 dari 3, dashboard/riwayat/profil pasien, booking online wajib akun.
- Checklist pengujian & UAT: `docs/UAT.md`. Rencana per sprint: `docs/PLAN.md`.

## Backup & pemulihan
- Backup manual: `php artisan sipat:backup` (otomatis tiap hari 21:00 WIB lewat scheduler, simpan 7 file terakhir).
- Pemulihan: `php artisan sipat:restore storage/app/backups/<file>.sql --force`
