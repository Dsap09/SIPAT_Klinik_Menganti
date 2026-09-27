# SIPAT — Sistem Antrian Online Terpadu Klinik Menganti

Aplikasi antrian klinik berbasis Laravel: pendaftaran online pasien umum, antrian terpadu BPJS + umum dalam satu nomor urut, dan dashboard monitoring.

Dokumen perencanaan dan rencana sprint ada di `docs/` (mulai dari `docs/PLAN.md`).

## Kebutuhan
- PHP >= 8.2 (diuji pada 8.4)
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

Akun staf hasil seeder: `admin`, `petugas`, `manajemen`, `dokter` — password `password`.

## Pengujian
```bash
php artisan test
vendor/bin/pint
```

## Status
- **Sprint 1 selesai**: pendaftaran online pasien umum + nomor antrean tunggal (A-001).
- Sprint 2-5: lihat `docs/PLAN.md`.
