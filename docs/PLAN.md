# Rencana Proyek SIPAT — Klinik Menganti

Dokumen ini adalah rujukan pelaksanaan teknis untuk membangun **SIPAT (Sistem Antrian Online Terpadu)**. Sumber: `Laporan_Proyek_SIPAT_Klinik_Menganti.docx`, `Product_Backlog_Kelompok4.xlsx`, `ERD.png`, dan `flowchat sipat.png`.

## 1. Tujuan & ruang lingkup
Membangun MVP v1.0 dengan tiga modul inti:
1. **Pendaftaran Online Pasien Umum** — pasien umum daftar dari rumah, dapat nomor antrean + estimasi.
2. **Antrian Terpadu** — BPJS (input manual dari Mobile JKN) dan umum dalam satu nomor urut tunggal.
3. **Dashboard** — jumlah pasien, status antrean, rekap harian.

Stack: **PHP/Laravel**, **MySQL**, **HTML/CSS/Bootstrap/JavaScript**. Metode: Agile/Scrum, 5 sprint.

## 2. Keputusan teknis
- **PK string sesuai ERD** (`ID_Poli`, `ID_Dokter`, dst.) — bukan auto-increment. Model: `$keyType='string'`, `$incrementing=false`.
- **Nama tabel/kolom persis ERD** (Indonesia) — wajib `protected $table`.
- **Tanpa kolom `created_at`/`updated_at`** — ERD tidak memilikinya, jadi `$timestamps=false`.
- **Auth staf** via `PENGGUNA` (username + password terenkripsi, role `Admin|Petugas|Manajemen|Dokter`). **Pasien umum tidak punya akun** — form publik + verifikasi No RM, akses status lewat `No_Antrean`.
- **Nomor antrean** `A-001` dibuat **atomik** (DB transaction + `lockForUpdate`) agar tidak ada duplikat.
- **Notifikasi** (5.1.1): MVP pakai polling halaman status, bukan websocket.
- **Backup** (6.1.4): Laravel Scheduler + `mysqldump`.
- **RBAC + audit trail** (6.1.1): middleware role + observer/event menulis ke `AUDIT_TRAIL`.
- Frontend memakai **Vite + Bootstrap**.

## 3. Model data (ERD)
| Tabel | Kolom kunci |
|---|---|
| `POLI` | ID_Poli (PK), Nama_Poli, Deskripsi |
| `DOKTER` | ID_Dokter (PK), Nama_Dokter, Spesialisasi, ID_Poli (FK) |
| `JADWAL` | ID_Jadwal (PK), Hari_Layanan, Jam_Mulai, Jam_Selesai, Kuota_Maksimal, Sisa_Kuota, ID_Poli (FK), ID_Dokter (FK) |
| `PASIEN` | ID_Pasien (PK), No_RM (unik, kosong utk pasien baru), Nama_Lengkap, Tgl_Lahir, Alamat, Jenis_Pasien (`UMUM`/`BPJS`), No_BPJS (kosong bila UMUM) |
| `ANTREAN` | ID_Antrean (PK), No_Antrean (`A-001`), Tanggal_Kunjungan, Estimasi_Waktu, Status (`Menunggu`/`Dilayani`/`Selesai`/`Batal`), Waktu_CheckIn, ID_Pasien (FK), ID_Jadwal (FK) |
| `PENGGUNA` | ID_Pengguna (PK), Username, Password (terenkripsi), Role, Nama_Pengguna |
| `AUDIT_TRAIL` | ID_Log (PK), Waktu_Akses, Entitas_Terdampak, Deskripsi_Aksi, ID_Pengguna (FK) |

## 4. Struktur aplikasi
- **Publik**: `GET|POST /daftar` (form pasien baru/lama), `GET /status/{noAntrean}`.
- **Loket (Petugas)**: input BPJS dari Mobile JKN, verifikasi/check-in, cetak kartu.
- **Admin**: CRUD poli/dokter/jadwal/kuota.
- **Manajemen**: `/dashboard`.
- **Dokter**: daftar pasien, ubah status `Menunggu → Dilayani → Selesai`.
- Lapisan: Controller → Service (`QueueService`, `EstimationService`) → Eloquent.

## 5. Rencana per Sprint
| Sprint | Item backlog | Fokus |
|---|---|---|
| 1 | 1.1.1, 2.1.1, 6.1.2 | Setup Laravel, migrasi + seeder, form pendaftaran umum, generator nomor antrean tunggal, layout responsif |
| 2 | 2.1.2, 2.1.3, 6.1.1 | Input manual BPJS, verifikasi check-in + cetak kartu, auth + RBAC + audit trail |
| 3 | 1.1.2, 3.1.1, 4.1.1 | Estimasi waktu, dashboard real-time, CRUD data master + kuota |
| 4 | 1.1.3, 5.1.1, 6.1.3, 6.1.4 | Batal/reschedule + pelepasan kuota, notifikasi, optimasi ≤3s, backup harian |
| 5 | 6.1.5 | Panduan pengguna, hardening, persiapan UAT |

## 6. Alur inti (flowchart)
- **Pasien umum**: buka web → pilih poli/jadwal → isi data (baru) / No RM (lama) → dapat `No_Antrean` + estimasi → check-in → dilayani → selesai.
- **Pasien BPJS**: petugas input dari Mobile JKN → masuk antrean tunggal → verifikasi (nama + tanggal lahir) → cetak kartu.
- **Sistem**: hitung estimasi, sinkronkan `Sisa_Kuota`, backup harian.
- **Admin** kelola master; **Manajemen** pantau dashboard.

## 7. Pengujian & kriteria terima
Black Box + UAT (minggu 13-14). Kriteria kunci:
- Tidak ada nomor antrean duplikat/tumpang tindih pada audit 1 hari.
- Angka dashboard sama persis dengan basis data (selisih 0).
- Notifikasi terkirim ≤60 detik setelah status berubah.
- Halaman utama dimuat ≤3 detik.
- Halaman mobile tanpa scroll horizontal.

## 8. Batasan (di luar lingkup)
Integrasi API Mobile JKN, manajemen stok obat, billing kompleks, integrasi SATUSEHAT penuh, mobile app native, AI diagnosis.

## 9. Timeline
Pengembangan minggu 8-12 (Sprint 1-5), pengujian 13-14, pelatihan 15, penutupan 16.

## 10. Status pelaksanaan
### Sprint 1 — selesai
- Laravel 13 di root repo, MySQL `sipat_klinik_menganti`, frontend Bootstrap 5 via Vite.
- 7 migrasi sesuai ERD (`POLI`, `DOKTER`, `JADWAL`, `PASIEN`, `PENGGUNA`, `ANTREAN`, `AUDIT_TRAIL`) + seeder.
- Pendaftaran online pasien umum (`/daftar`, pasien baru/lama) + halaman konfirmasi (`/status/{noAntrean}`).
- Nomor antrean tunggal `A-001` dibuat atomik (`Cache::lock` + transaksi + unique index `(Tanggal_Kunjungan, No_Antrean)`).
- 9 feature test lulus (`php artisan test`).

### Berikutnya
- Sprint 2: input BPJS manual, verifikasi check-in + cetak kartu, auth + RBAC + audit trail.

