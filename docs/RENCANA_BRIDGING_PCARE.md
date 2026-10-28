# Rencana Bridging BPJS PCare — SIPAT Klinik Menganti

Status: **RENCANA / BELUM DIIMPLEMENTASIKAN**
Disusun: 19 Oktober 2026
Sifat: usulan pasca-MVP (di luar baseline v1.0), perlu change request.

Dokumen ini merangkum rencana penambahan fitur **bridging PCare (FKTP)** ke SIPAT:
mengelola kredensial, menguji koneksi, menarik data peserta BPJS, dan mengisi
otomatis form antrean loket. Tidak ada perubahan kode yang dilakukan sampai
rencana ini disetujui dan kredensial tersedia.

---

## 1. Latar belakang dan tujuan

SIPAT saat ini menangani pasien BPJS secara **manual**: petugas menyalin data dari
Mobile JKN ke form loket. Rencana ini menambahkan lapisan bridging yang opsional
untuk:

1. Menyimpan kredensial PCare yang diterbitkan BPJS.
2. Menguji koneksi ke layanan PCare.
3. Mencari data peserta berdasarkan No. Kartu atau NIK.
4. Mengisi otomatis form input pasien BPJS di panel loket.

Fitur ini **tidak menggantikan** alur manual. Jika kredensial belum ada, tidak
valid, atau BPJS sedang gangguan, SIPAT tetap berjalan seperti sekarang.

---

## 2. Keputusan yang sudah disepakati

| Aspek | Keputusan |
|---|---|
| Layanan | **PCare (FKTP)** sebagai driver utama |
| Lingkup iterasi pertama | Halaman kredensial + tes koneksi + tarik peserta + autofill form antrean |
| Pengelola kredensial | **Admin** saja |
| Pengguna lookup | **Petugas + Admin** |
| Kolom baru di `PASIEN` | **Tidak ada**; status/kelas peserta hanya ditampilkan sementara |
| Pasien duplikat | Dipakai ulang berdasarkan `NIK` atau `No_BPJS` |
| Status fitur | **Nonaktif secara default**; fallback ke alur manual |
| Driver VClaim | Tidak diimplementasikan pada iterasi ini; arsitektur disiapkan untuk penambahan |

---

## 3. Prasyarat dan hal yang perlu dikonfirmasi

Sebelum implementasi dan terutama sebelum go-live, perlu dipastikan:

1. **Jenis registrasi klinik**: PCare untuk FKTP; VClaim untuk FKRTL. Rencana ini
   mengasumsikan klinik adalah FKTP.
2. **Kredensial PCare** yang diterbitkan BPJS:
   - Cons ID
   - Secret Key
   - User Key
   - Username PCare
   - Password PCare
   - Kd Aplikasi
3. **Versi/alamat layanan PCare** yang diberikan BPJS, misalnya:
   - `pcare-rest` (production)
   - `pcare-rest-dev` (development)
   - `pcare-rest-v3.0` (jika diarahkan ke versi baru)
4. **Mode autentikasi**: sebagian instalasi memakai
   `X-authorization: Basic base64(username:password:kdAplikasi)`; versi lain
   memakai Bearer token. Rencana awal memakai **Basic**, dengan `Auth_Mode`
   disiapkan untuk token.
5. **Izin akses**: kredensial produksi biasanya dipegang IT/vendor klinik. Untuk
   proyek akademik, gunakan kredensial development atau `Http::fake()`.
6. **Whitelist/aktivasi layanan** (jika disyaratkan BPJS) perlu dikonfirmasi ke
   klinik/BPJS.

Tanpa kredensial asli, fitur dapat dibangun dan diuji dengan respons palsu, tetapi
**tidak dapat diverifikasi live**.

---

## 4. Arsitektur

```text
Filament Page (Admin)
        |
BpjsBridgingService
        |
BpjsClient (signature, HTTP, decrypt) ---> PCare API
        |
DataPeserta (DTO + mapping)
        |
AntreanForm (Petugas) ---> CreateAntrean ---> QueueService
```

Prinsip:

- Controller/page tidak memanggil API secara langsung.
- Semua detail protokol PCare terisolasi di `BpjsClient` dan provider PCare.
- Mapping ke kolom internal hanya di `DataPeserta`.
- Kegagalan BPJS tidak boleh membuat SIPAT error; tampilkan pesan yang aman dan
  biarkan alur manual berjalan.

Komponen yang direncanakan:

- `app/Services/Bpjs/BpjsClient.php`
- `app/Services/Bpjs/BpjsBridgingService.php`
- `app/Services/Bpjs/Providers/PcareProvider.php`
- `app/Services/Bpjs/DataPeserta.php`
- `app/Services/Bpjs/BpjsException.php`
- `app/Support/LzString.php`
- `app/Models/PengaturanBpjs.php`
- `app/Filament/Pages/PengaturanBpjs.php`
- `resources/views/filament/pages/pengaturan-bpjs.blade.php`
- `config/bpjs.php`

---

## 5. Skema database

Tabel baru `PENGATURAN_BPJS` (satu baris, `ID_Pengaturan = 'BPJS'`):

| Kolom | Tipe | Catatan |
|---|---|---|
| `ID_Pengaturan` | string PK | `BPJS` |
| `Layanan` | string | default `PCare` |
| `Cons_ID` | string | tidak dienkripsi |
| `Secret_Key` | text | cast `encrypted` |
| `User_Key` | text | cast `encrypted` |
| `Username` | string | username PCare |
| `Password` | text | cast `encrypted` |
| `Kd_Aplikasi` | string | kode aplikasi dari BPJS |
| `Auth_Mode` | string | default `basic`; opsi `token` untuk masa depan |
| `Mode` | string | `development` / `production` |
| `Aktif` | boolean | default `false` |
| `Status_Terakhir` | string | `terhubung` / `gagal` / null |
| `Pesan_Terakhir` | text | pesan aman, tanpa secret |
| `Dicek_Pada` | datetime | waktu tes terakhir |

Konvensi mengikuti model proyek: PK string, tanpa `created_at`/`updated_at`.
`Secret_Key`, `User_Key`, dan `Password` memakai tipe `text` karena hasil enkripsi
bisa melebihi 255 karakter.

---

## 6. Kredensial dan header PCare

Header yang dikirim:

```text
X-cons-id: {Cons_ID}
X-timestamp: {unix timestamp}
X-signature: {base64(HMAC-SHA256(Cons_ID + "&" + timestamp, Secret_Key))}
user_key: {User_Key}
X-authorization: Basic {base64(Username + ":" + Password + ":" + Kd_Aplikasi)}
Accept: application/json
```

Catatan:

- Timestamp memakai Unix timestamp UTC (`now()->timestamp`), bukan jam lokal
  terformat.
- Header `X-authorization` hanya dikirim jika `Auth_Mode = basic` dan ketiga
  komponennya terisi.
- Jika di kemudian hari diperlukan Bearer token, tambahkan langkah ambil token
  dan cache sampai kedaluwarsa tanpa mengubah form antrean.

---

## 7. Enkripsi respons

PCare v2 dapat mengembalikan `response` berupa string terenkripsi. Alur dekripsi:

```text
key      = Cons_ID + Secret_Key + timestamp
keyHash  = hex2bin(hash('sha256', key))
iv       = substr(keyHash, 0, 16)
plain    = openssl_decrypt(base64_decode(response), 'AES-256-CBC', keyHash, OPENSSL_RAW_DATA, iv)
json     = LzString::decompressFromEncodedURIComponent(plain)
```

Jika `response` sudah berupa array/JSON polos, gunakan langsung. Jika dekripsi
atau dekompresi gagal, tampilkan pesan "Respons BPJS tidak dapat dibaca" dan
jangan menampilkan data mentah.

`LzString` akan diimplementasikan sebagai support class internal (port
`decompressFromEncodedURIComponent`) agar tidak menambah dependensi baru, dengan
fixture dari implementasi referensi JavaScript.

---

## 8. Endpoint yang dipakai

| Keperluan | Method | Path |
|---|---|---|
| Tes koneksi | GET | `poli/fktp/0/10` |
| Cari peserta berdasarkan No. Kartu | GET | `peserta/noka/{noKartu}` |
| Cari peserta berdasarkan NIK | GET | `peserta/nik/{nik}` |

Base URL default (dapat di-override lewat `.env`):

- Development: `https://apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev`
- Production: `https://apijkn.bpjs-kesehatan.go.id/pcare-rest`

Path tes koneksi dan service name dibuat configurable karena versi PCare dapat
berbeda.

---

## 9. Pemetaan data peserta

| Field PCare | Field SIPAT | Catatan |
|---|---|---|
| `peserta.nama` | `PASIEN.Nama_Lengkap` | — |
| `peserta.tglLahir` | `PASIEN.Tgl_Lahir` | format `Y-m-d` |
| `peserta.noKartu` | `PASIEN.No_BPJS` | selalu string, jangan di-cast ke int |
| `peserta.nik` | `PASIEN.NIK` | — |
| `peserta.sex` (`L`/`P`) | `PASIEN.Jenis_Kelamin` | `Laki-laki` / `Perempuan` |
| `peserta.mr.noTelepon` | `PASIEN.No_Telepon` | hanya jika kosong dan tidak bentrok |
| `peserta.statusPeserta` | tampilan sementara | tidak disimpan |
| `peserta.hakKelas` | tampilan sementara | tidak disimpan |
| `peserta.jenisPeserta` | tampilan sementara | tidak disimpan |
| `peserta.provUmum` | tampilan sementara | tidak disimpan |
| — | `PASIEN.Jenis_Pasien = 'BPJS'` | — |

DTO dibuat toleran terhadap variasi respons:

- `response.peserta` atau `response` langsung.
- `sex` atau `jenisKelamin`.
- `statusPeserta` atau `ketStatusPeserta`.
- `hakKelas` atau `nmKelas`.
- `jenisPeserta` atau `nmJenisPeserta`.

---

## 10. Alur penggunaan

1. Admin membuka `/panel/pengaturan-bpjs`, mengisi kredensial, menyimpan, lalu
   menekan **Tes Koneksi**.
2. Hasil tes disimpan sebagai `Status_Terakhir` + `Pesan_Terakhir` + `Dicek_Pada`.
3. Petugas membuka **Loket > Antrean > Input Pasien (BPJS / Walk-in)**, memilih
   jenis BPJS, memasukkan 13 digit No. Kartu atau 16 digit NIK, lalu menekan
   **Cek BPJS**.
4. Service memanggil endpoint peserta, mendekripsi respons, dan mengembalikan DTO.
5. Form terisi otomatis (nama, tanggal lahir, NIK, No. BPJS, jenis kelamin).
   Status/kelas peserta ditampilkan di form/notifikasi.
6. Jika pasien sudah terdaftar, tampilkan No RM dan gunakan record tersebut.
7. Submit tetap melalui `CreateAntrean` dan `QueueService` seperti sekarang.

---

## 11. Deduplikasi pasien

Saat lookup berhasil, cari `Pasien` berdasarkan `No_BPJS` atau `NIK`:

- Jika ditemukan:
  - gunakan `ID_Pasien` dan `No_RM` yang ada;
  - isi hanya field yang masih kosong;
  - jangan menimpa `No_Telepon` yang sudah ada;
  - jika `No_BPJS`/`NIK` tersimpan berbeda dengan hasil lookup, **blokir** dengan
    pesan validasi; jangan menggabungkan data yang tidak cocok.
- Jika tidak ditemukan:
  - buat pasien baru dengan No RM otomatis melalui `RekamMedisService`.

Field `NIK` pada form disimpan sebagai hidden field yang diisi dari hasil lookup,
tetap divalidasi di server.

---

## 12. Error dan fallback

| Kondisi | Perilaku |
|---|---|
| Bridging nonaktif / kredensial belum lengkap | Tombol lookup memberi info; alur manual tetap bisa |
| HTTP 401 | "Kredensial ditolak BPJS. Periksa Cons ID, Secret Key, User Key, Username, Password, dan Kd Aplikasi." |
| HTTP 403 | "Akses ditolak BPJS. Pastikan faskes terdaftar dan layanan PCare aktif." |
| Timeout / koneksi gagal | "BPJS tidak merespons. Pendaftaran manual tetap dapat dilakukan." |
| Dekripsi/dekompresi gagal | "Respons BPJS tidak dapat dibaca. Hubungi Admin." |
| Peserta tidak ditemukan | "Peserta tidak ditemukan. Periksa No. Kartu/NIK." |
| Peserta tidak aktif | Tampilkan data + peringatan "Status peserta TIDAK AKTIF" |

Fitur **tidak** dinonaktifkan otomatis hanya karena satu kegagalan sementara.
Admin dapat mematikan toggle `Aktif` secara manual.

---

## 13. Keamanan dan audit

- `Secret_Key`, `User_Key`, dan `Password` disimpan dengan cast `encrypted`.
- Halaman kredensial hanya dapat diakses Admin melalui `canAccess()`.
- Field secret tidak ditampilkan kembali setelah disimpan; kosongkan jika tidak
  diubah.
- Jangan pernah menulis secret, header, atau isi respons mentah ke log.
- Audit trail:
  - perubahan kredensial (tanpa nilai secret);
  - tes koneksi (berhasil/gagal);
  - lookup peserta (nomor kartu dimasking).
- Rate limit:
  - lookup peserta: 20/menit per pengguna;
  - tes koneksi: 5/menit per pengguna.
- Timeout HTTP 10 detik, connect timeout 5 detik, TLS verification aktif.
- `APP_KEY` wajib dibackup; jika berubah, secret terenkripsi tidak dapat dibaca.
- Backup harian yang sudah ada otomatis menyertakan tabel baru.

---

## 14. File yang direncanakan

Baru:

- `database/migrations/2026_10_19_000001_create_pengaturan_bpjs_table.php`
- `app/Models/PengaturanBpjs.php`
- `app/Services/Bpjs/BpjsClient.php`
- `app/Services/Bpjs/BpjsBridgingService.php`
- `app/Services/Bpjs/Providers/PcareProvider.php`
- `app/Services/Bpjs/DataPeserta.php`
- `app/Services/Bpjs/BpjsException.php`
- `app/Support/LzString.php`
- `app/Filament/Pages/PengaturanBpjs.php`
- `resources/views/filament/pages/pengaturan-bpjs.blade.php`
- `config/bpjs.php`
- `tests/Unit/LzStringTest.php`
- `tests/Unit/BpjsClientTest.php`
- `tests/Unit/DataPesertaTest.php`
- `tests/Feature/PengaturanBpjsTest.php`
- `tests/Feature/BpjsBridgingTest.php`

Diubah:

- `app/Filament/Resources/Antreans/Schemas/AntreanForm.php`
- `app/Filament/Resources/Antreans/Pages/CreateAntrean.php`
- `app/Providers/AppServiceProvider.php` (rate limiter)
- `resources/views/filament/pages/panduan.blade.php`
- `.env.example` (base URL/timeout, bukan secret)
- `AGENTS.md`, `docs/PLAN.md`, `docs/UAT.md`, `README.md`

---

## 15. Pengujian

Unit:

- LZ-String decompress dengan fixture referensi.
- Signature deterministik (`Carbon::setTestNow`).
- Dekripsi fixture PCare.
- Mapping DTO untuk variasi respons.

Feature:

- Admin dapat membuka/menyimpan kredensial; peran lain ditolak.
- Secret terenkripsi di database; field kosong tidak menimpa secret lama.
- Tes koneksi sukses/gagal memperbarui status dan audit.
- Lookup No. Kartu (13 digit) dan NIK (16 digit) mengisi form.
- Bridging nonaktif, 401, timeout, dan respons rusak menghasilkan pesan aman.
- Pasien lama dengan NIK/No_BPJS sama dipakai ulang; konflik diblokir.
- Alur UMUM dan tes loket lama tetap lulus.

Semua tes memakai `Http::fake()`; tidak ada panggilan API asli di CI.

---

## 16. Dokumentasi dan change control

- Tambahkan addendum "Bridging BPJS PCare (opsional, pasca-MVP)" di `docs/PLAN.md`.
- Tambahkan bagian C di `docs/UAT.md` dengan status opsional/blocked sampai
  kredensial tersedia.
- Perbarui `AGENTS.md` (tabel baru, route, keamanan, catatan PCare/FKTP).
- Perbarui panduan pengguna di panel.
- Catat sebagai change request terhadap baseline v1.0; jangan mengubah PDF
  `System_Modelling_dan_Prototyping_Kelompok4.pdf` yang sudah dibekukan.
- Backlog `Product_Backlog_Kelompok4.xlsx` dapat ditambah user story baru secara
  manual bila fitur ini disetujui.

---

## 17. Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Klinik ternyata FKRTL/VClaim | Driver PCare gagal dengan pesan jelas; tambahkan driver VClaim di belakang interface |
| Versi PCare berbeda (Basic vs Bearer) | `Auth_Mode` dan base URL configurable; verifikasi dengan kredensial asli |
| Respons PCare tidak terduga | DTO toleran; fixture nyata; pesan aman |
| Tanpa kredensial asli | Fitur tetap nonaktif; tes memakai fake; live test manual |
| Kredensial bocor | Enkripsi, masking, audit, tanpa log PII |
| `APP_KEY` hilang | Dokumentasikan backup `APP_KEY` |
| Konflik NIK/No_Telepon unik | Validasi server + blokir konflik |
| Perubahan `Jenis_Pasien` menjadi BPJS | Pasien kehilangan akses booking online; dokumentasikan ke petugas |
| BPJS downtime | Timeout + fallback manual |

---

## 18. Yang tidak dikerjakan pada iterasi ini

- SEP, rujukan, dan sinkronisasi referensi poli.
- Integrasi Mobile JKN / Antrean FKTP.
- Kolom baru di `PASIEN` (`Status_BPJS`, `Kelas_BPJS`, dsb.).
- Driver VClaim.
- Perubahan halaman pasien.
- Auto-disable permanen saat terjadi kegagalan sementara.

---

## 19. Urutan eksekusi (setelah disetujui)

1. Konfirmasi PCare/FKTP, versi layanan, dan ketersediaan kredensial.
2. Migrasi + model `PengaturanBpjs` + tes enkripsi.
3. `config/bpjs.php`, `LzString`, `BpjsClient` + tes.
4. `BpjsBridgingService`, `PcareProvider`, `DataPeserta` + tes.
5. Halaman Filament + view + tes akses/enkripsi/tes koneksi.
6. Integrasi `AntreanForm` + dedupe `CreateAntrean` + tes.
7. Rate limit, audit, dan penyempurnaan pesan error.
8. Update dokumentasi dan panduan.
9. `php artisan migrate`, `php artisan test`, `vendor/bin/pint`.
10. Uji manual dengan kredensial development/production bila tersedia.

---

## 20. Keputusan yang masih terbuka

- Apakah klinik benar-benar FKTP/PCare (bukan FKRTL/VClaim)?
- Apakah PCare memakai Basic auth atau Bearer token?
- Base URL/service name PCare yang diberikan BPJS.
- Apakah kredensial development tersedia untuk pengujian?
- Apakah fitur ini disetujui sebagai change request pasca-MVP atau ditunda
  sebagai future work?
