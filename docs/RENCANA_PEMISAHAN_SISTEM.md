# Rencana Pemisahan Sistem Pasien vs Staf - SIPAT Klinik Menganti

Dokumen ini merangkum rencana pemisahan sistem pasien dan staf setelah diverifikasi terhadap kondisi codebase. Fase 1–2 **sudah diimplementasi di repo ini**; fase 3 (deployment) berupa panduan operasional, bukan refactor besar.

## 1. Definisi Batas Sistem

| **Aspek** | **Sistem Pasien (Frontend)** | **Sistem Staf/Admin (Backend)** |
|---|---|---|
| **Pengguna** | Pasien umum & pasien lama (akun pasien, guard `pasien`) | Petugas, Admin, Dokter, Manajemen (tabel `PENGGUNA`, guard `web`) |
| **Fungsi utama** | Buat akun, daftar berobat, pantau status, batal/jadwal-ulang antrean sendiri | Input BPJS/walk-in, check-in, cetak kartu, ubah status, data master, dashboard |
| **Data yang diakses** | Hanya antrean/profil milik sendiri (dipaksa lewat uji kepemilikan) | Semua data pasien, dengan RBAC + audit trail |
| **Frekuensi akses** | Tinggi (publik) | Sedang (internal) |
| **Toleransi downtime** | Sedang (fallback: walk-in lewat petugas) | Rendah (operasional klinik) |
| **Kebutuhan keamanan** | Autentikasi 2-dari-3, rate limit | RBAC, audit trail, whitelist IP saat deploy terpisah, backup harian |

## 2. Koreksi Terhadap Dugaan Awal

Beberapa dugaan pada rencana awal perlu diluruskan karena sudah terwujud di codebase:

| **Dugaan awal** | **Kondisi sebenarnya** |
|---|---|
| "Sebelumnya satu guard `web`" | Dua guard sudah terpisah sejak lama: `pasien` (akun pasien, tanpa password) dan `web` (staf → `PENGGUNA`). |
| "Buat guard `staf` → tabel `users`" | Tidak dilakukan dan tidak direkomendasikan: staf memang ada di `PENGGUNA` (provider `pengguna`), tabel `users` tidak dipakai. Guard `web` = guard staf; tidak ada rename. |
| "Rate limit belum ada" | Sudah ada 3 limiter (`pendaftaran`, `status-antrean`, `pasien-login`) dari `config/sipat.php`, dengan test yang memastikan respons 429. |
| "Pisahkan controller pasien/staf" | Sudah terpisah: `PendaftaranController` + `App\Http\Controllers\Pasien\*` untuk pasien; sisi staf berupa resource Filament. |
| Deploy frontend pasien ke Vercel | Vercel tidak ramah Laravel. Solusinya: satu codebase, dua **instance** dengan env `SIPAT_MODE`. |
| Dua session driver terpisah | Dua guard hidup bersama dalam satu sesi tanpa saling menggantikan (ada uji eksplisit). Saat nanti di-deploy dua domain berbeda, `SESSION_DOMAIN` membuat cookie kedua domain terpisah secara alami. |

## 3. Keputusan Kunci

| **Keputusan** | **Pilihan** | **Alasan** |
|---|---|---|
| Repo | **Satu repo** | Model, `QueueService`, migrasi, dan seeder dipakai kedua sisi; dua repo berarti salinan logika yang bisa berbeda (nomor antrean/kuota jadi rawan). |
| Deployment | **Satu codebase, dua instance** (`SIPAT_MODE=pasien` / `staf`) | Isolasi runtime tanpa pecah kode: instance pasien mematikan panel staf, instance staf mematikan halaman publik. |
| Database | **Satu DB bersama** | Konsistensi data antrean & kuota. |
| Auth | **Guard tetap `web` (staf) + `pasien`** | `AuditLogger`, Filament, `EnsureRole`, dan kolom `sessions.user_id` bergantung pada nama guard yang ada. |
| Isolasi data antrean | **Batal/jadwal-ulang wajib login + kepemilikan; halaman status tetap publik (baca saja)** | Selama ini siapa pun yang mengetahui nomor antrean bisa membatalkan/menjadwalkan ulang antrean orang lain - ini celah yang ditutup. |
| API layer (`routes/api.php`) | **Ditunda** | Monolit Blade cukup saat ini; API JSON dibuat hanya bila frontend pasien berpindah stack. |
| Fallback | **Walk-in via petugas** | Bila sisi online turun, loket tetap melayani. |

## 4. Arsitektur Target

```
┌────────────────────────────────────────────────┐
│  Instance pasien (hosting publik)              │
│  SIPAT_MODE=pasien                             │
│  - Halaman publik + akun pasien aktif          │
│  - /panel, /kartu → 404 (dimatikan)            │
│  - sipat.klinikmenganti.com (HTTPS)            │
└──────────────────┬─────────────────────────────┘
                   │ HTTPS → MySQL terkelola
                   ▼
┌────────────────────────────────────────────────┐
│  Database bersama                              │
│  sipat_klinik_menganti                         │
└──────────────────┬─────────────────────────────┘
                   │ koneksi internal
                   ▼
┌────────────────────────────────────────────────┐
│  Instance staf (jaringan klinik / VPS)         │
│  SIPAT_MODE=staf                               │
│  - Panel Filament penuh + kartu antrian        │
│  - /, /status, /pasien/* → 404 (dimatikan)     │
│  - panel.klinikmenganti.com,                   │
│    hanya diakses dari IP klinik (whitelist)    │
└────────────────────────────────────────────────┘
```

Prinsip: **kodenya satu, instalasinya dua**. Perbaikan logika antrean cukup sekali dan berlaku di kedua sisi, tetapi biner yang berjalan di server benar-benar terisolasi.

## 5. Yang Sudah Diimplementasi (Fase 1–2)

| **#** | **File** | **Perubahan** |
|---|---|---|
| 1 | `config/sipat.php`, `.env.example` | Tambah `mode => env('SIPAT_MODE', 'full')` - nilai `pasien`, `staf`, atau `full` (default: keduanya, perilaku lama). |
| 2 | `app/Http/Middleware/EnsureMode.php` (baru) | Middleware `mode:pasien` / `mode:staf` - 404 bila mode instance tidak cocok. |
| 3 | `bootstrap/app.php` | Alias `mode`; plus `prependToPriorityList(AuthenticatesRequests::class, EnsureMode::class)` agar cek mode selalu berjalan sebelum middleware `auth` (lihat bagian 8). |
| 4 | `routes/web.php` | Semua rute pasien/publik dibungkus `mode:pasien`; batal & jadwal-ulang pindah ke `/pasien/antrean/{noAntrean}/...` (di dalam prefix `pasien` sehingga tamu diarahkan ke login pasien; nama rute tidak berubah); `/kartu` ditambah `mode:staf`. |
| 5 | `app/Http/Controllers/PendaftaranController.php` | `batal`, `formJadwalUlang`, `jadwalUlang` memanggil `wajibMilikPemilik()` → 403 bila `ID_Pasien` antrean ≠ `auth('pasien')->id()`. Halaman status dan endpoint polling (`/status/{no}/data`) tetap publik (baca saja). |
| 6 | `AdminPanelProvider` | `EnsureMode::class.':staf'` di depan middleware panel. |
| 7 | `resources/views/pendaftaran/status.blade.php` | Tamu melihat kartu ajakan "masuk dulu"; hanya pasien yang sudah login yang melihat tombol batal/jadwal-ulang. |
| 8 | `resources/views/filament/pages/panduan.blade.php` | Panduan panel diperbarui sesuai aturan baru. |

Panduan deployment per instance (fase 3):

- **Instance pasien** → set `SIPAT_MODE=pasien`, lalu `php artisan config:cache`.
- **Instance staf** → set `SIPAT_MODE=staf`, `php artisan config:cache`, dan lindungi dengan **whitelist IP klinik** di level web server (nginx `allow/deny` atau `Require ip` di Apache). Panel tetap memerlukan username/password `PENGGUNA`.
- **HTTPS** wajib di kedua sisi; isi **`SESSION_DOMAIN`** per subdomain bila kedua instance masih menerima login dari peramban yang sama.
- `/up` (health check) tetap hidup di kedua mode karena tidak masuk grup `mode`.

## 6. Pengujian Terkait

Matriks dari rencana lama → bukti di codebase (semua hijau di `php artisan test`, 93 test):

| **Pengujian** | **Test terkait** |
|---|---|
| Pasien B tidak bisa batal/jadwal-ulang antrean pasien A (403) | `PembatalanTest::test_antrean_milik_pasien_lain_tidak_bisa_dibatalkan_atau_dijadwalkan_ulang` |
| Tamu diarahkan ke login pasien saat mencoba batal | `PembatalanTest::test_tamu_tidak_bisa_membatalkan_dan_diarahkan_ke_login_pasien` |
| Pemilik tetap bisa batal/jadwal-ulang; kuota & audit tetap benar | `PembatalanTest` (batal, batal dua kali, jadwal-ulang, kuota tujuan habis) |
| Sesi pasien tidak membuka panel staf | `PemisahanSistemTest::test_sesi_pasien_tidak_membuka_panel_staf` |
| Dua guard aktif bersamaan tanpa saling menggantikan | `PemisahanSistemTest::test_sesi_pasien_dan_staf_dapat_aktif_bersamaan_...` |
| Mode pasien: seluruh panel & kartu staf 404 | `PemisahanSistemTest::test_mode_pasien_mematikan_panel_staf_dan_kartu` |
| Mode staf: halaman publik pasien 404, panel tetap hidup | `PemisahanSistemTest::test_mode_staf_mematikan_seluruh_halaman_publik_pasien` |
| Rate limit endpoint publik (429) | `AkunPasienTest`, `AuditAntreanTest` |

Skema proteksi aksi mutasi antrean punya **tiga lapis**: (1) mode instance, (2) login guard `pasien`, (3) uji kepemilikan - dan semua mutasi tetap satu jalan melalui `QueueService` agar kunci kuota dan nomor antrean konsisten.

## 7. Timeline Realistis

| **Tahap** | **Aktivitas** | **Status** |
|---|---|---|
| Minggu 1–2 | Audit rute, guard, dan celah publik; refactor di repo | **Selesai** (implementasi menyusuri dokumen ini) |
| Minggu 3 | Pengujian isolasi + perbaikan | **Selesai** (test suite hijau) |
| Minggu 4 | Dua instance terpisah + domain + HTTPS + whitelist IP | Opsional - hanya konfigurasi server, kode sudah siap |
| Minggu 5–6 | UAT akhir + dokumentasi | Mengikuti kalender UAT di `docs/UAT.md` |

## 8. Catatan Teknis (gotcha untuk perawatan)

- **Priority middleware**: Laravel mengurutkan middleware autentikasi berdasarkan **kontrak** (`Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests`), bukan classnya. Karena itu di `bootstrap/app.php` dipakai `prependToPriorityList(AuthenticatesRequests::class, EnsureMode::class)`. Bila baris ini dihapus, middleware `auth` akan berjalan sebelum cek mode di `/kartu` sehingga tamu mendapat *redirect login*, bukan 404, pada instance yang salah mode. Gunakan kontraknya, bukan `Illuminate\Auth\Middleware\Authenticate::class`.
- **Grup rute bernama**: rute di dalam `Route::prefix('pasien')->name('pasien.')` otomatis mendapat prefix `pasien.` pada nama rute. Rute batal/jadwal-ulang yang namanya harus tetap `pendaftaran.*` ditempatkan di **grup prefix terpisah** tanpa prefix nama, meski URL-nya tetap di bawah `/pasien/...`.
- **Uji kepemilikan** memakai perbandingan ketat string (PK bertipe string): `ID_Pasien === auth('pasien')->id()`. Mutasi antrean tidak boleh berpaling dari `QueueService::batalkan()/jadwalkanUlang()` karena kunci konkurensi kuota hanya ada di service.
- **Dokumentasi pemakaian**: catat `SIPAT_MODE` pada tiap lingkungan (dev = `full`) supaya perilaku 404 tidak mengagetkan saat demo.

## 9. Risiko dan Mitigasi

| **Risiko** | **Mitigasi** |
|---|---|
| Salah set `SIPAT_MODE` saat deploy | Default `full` berperilaku seperti sekarang; setelah deploy, cek `/panel` dan `/` di kedua instance (yang tidak berperan harus 404). |
| Pasien tanpa akun ingin ubah jadwal | Kartu status menampilkan ajakan masuk; loket tetap bisa batal/jadwal-ulang untuk pasien walk-in/BPJS. |
| Pemegang nomor antrean orang lain mencoba aksi | 403 lewat uji kepemilikan - nomor antrean bukan lagi kunci mutasi. |
| Downtime saat migrasi deployment/backup | Lakukan di luar jam operasional; salin `.env` + `php artisan config:clear` bila berpindah mode; restore dari backup harian bila perlu (`php artisan sipat:restore`). |
| Biaya hosting naik | Kecil: satu VPS dengan dua virtualhost berbeda dalam satu kode, database berjalan di MySQL terkelola yang sama. |
