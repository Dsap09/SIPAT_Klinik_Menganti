# Panduan Pengujian & UAT — SIPAT Klinik Menganti

Checklist Black Box + User Acceptance Testing (minggu 13-14). Diturunkan dari `Product_Backlog_Kelompok4.xlsx` dan indikator keberhasilan pada `Laporan_Proyek_SIPAT_Klinik_Menganti.docx`.

## Persiapan

1. `php artisan migrate:fresh --seed` untuk menyiapkan basis data uji.
2. `npm run build` lalu `php artisan serve`.
3. (Opsional) `php artisan schedule:work` untuk menguji backup otomatis harian.
4. `php artisan test` — menjalankan seluruh pengujian otomatis (Black Box fungsional).

Akun uji: `admin`, `petugas`, `manajemen`, `dokter` — password `password`.

## A. Pendaftaran Online Pasien Umum

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| A1 | Pasien baru mendaftar | Buka `/daftar`, pilih jadwal, pilih "Pasien baru", isi nama/tanggal lahir/alamat, submit | Dapat nomor `A-001` dan halaman status; kuota jadwal berkurang 1 |
| A2 | Pasien lama dengan No RM | Pilih "Pasien lama", masukkan No RM yang terdaftar | Nomor antrean terbit, data pasien tidak terduplikasi |
| A3 | No RM tidak valid | Pilih "Pasien lama", masukkan No RM acak | Validasi menolak: "No RM tidak ditemukan" |
| A4 | Kuota habis | Habiskan kuota sebuah jadwal, lalu daftar ke jadwal itu | Pendaftaran ditolak: "Kuota jadwal ini sudah habis" |
| A5 | Estimasi waktu | Daftar lalu lihat halaman status | Estimasi tampil (jam mulai + 15 menit per pasien, dibatasi jam selesai) |

## B. Antrian Terpadu (BPJS + Umum)

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| B1 | Input BPJS manual | Panel `/panel` → Loket → Antrean → Input Pasien, jenis BPJS, isi No BPJS | Nomor antrean terbit pada urutan tunggal (melanjutkan nomor umum) |
| B2 | Walk-in umum | Sama, jenis "Umum (walk-in)" | Nomor terbit, `No_BPJS` kosong |
| B3 | Tidak ada duplikat | Daftarkan 20+ pasien campuran umum/BPJS dalam satu hari, lalu periksa daftar antrean | Tidak ada nomor yang sama pada tanggal yang sama; urutan berurutan `A-001`, `A-002`, ... |
| B4 | Kuota turun sesuai | Bandingkan "Sisa Kuota" di Data Master dengan jumlah antrean aktif | Sisa = kuota maksimal − antrean non-Batal |

Bukti otomatis: `AuditAntreanTest::test_tidak_ada_nomor_antrean_duplikat_atau_tumpang_tindih_dalam_sehari`.

## C. Check-in dan Kartu Antrian

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| C1 | Verifikasi cocok | Klik Check-in, masukkan tanggal lahir yang benar | Check-in tersimpan, kartu antrian terbuka otomatis untuk dicetak |
| C2 | Verifikasi gagal | Klik Check-in, masukkan tanggal lahir yang salah | Check-in ditolak, status tetap Menunggu |
| C3 | Kartu | Klik tombol Kartu | Kartu memuat nomor, nama, poli, dokter, jadwal, dan waktu check-in |
| C4 | Ubah status | Ubah Status: Menunggu → Dilayani → Selesai | Daftar antrean dan dashboard mengikuti status terbaru |
| C5 | Akses kartu | Buka `/kartu/A-001` tanpa login / sebagai Dokter | Tamu dialihkan ke login panel; Dokter mendapat 403 |

## D. Data Master, Dashboard, Batal/Jadwal Ulang

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| D1 | CRUD master | Admin menambah Poli, Dokter, Jadwal | ID otomatis (`POLI-xx`, `DOK-xx`, `JDW-xx`); perubahan kuota langsung berlaku |
| D2 | Batas peran | Login Petugas, buka menu Data Master | Ditolak (403); menu tidak muncul |
| D3 | Dashboard | Buka Dashboard sebagai Admin/Manajemen | Angka total/BPJS/umum/status sama persis dengan daftar antrean |
| D4 | Batal | Di halaman status, klik "Batalkan Pendaftaran" | Status menjadi Batal, kuota kembali, tercatat di Audit Trail |
| D5 | Jadwal ulang | Klik "Jadwalkan Ulang", pilih jadwal baru | Antrean lama Batal (kuota lepas), nomor baru terbit di jadwal tujuan |
| D6 | Tidak bisa batal setelah check-in | Setelah check-in, buka halaman status | Tombol batal/jadwal ulang tidak muncul; permintaan langsung ditolak |

## E. Notifikasi, Keamanan, Backup, Performa

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| E1 | Notifikasi ≤60 detik | Buka `/status/{nomor}`, ubah status dari panel, tunggu ≤30 detik | Banner "Status antrean Anda berubah" muncul, badge dan estimasi ikut berubah |
| E2 | Audit trail | Ubah data pasien / lakukan check-in, periksa tabel `AUDIT_TRAIL` | Tercatat entitas, aksi, waktu, dan pengguna pelaku |
| E3 | RBAC | Buka halaman panel sesuai peran | Setiap peran hanya melihat menu yang diizinkan |
| E4 | Rate limit | Kirim >20 pendaftaran/menit dari satu IP | Permintaan berikutnya dibalas `429 Too Many Requests` |
| E5 | Backup | `php artisan sipat:backup` lalu `php artisan sipat:restore <file> --force` | File `.sql` terbentuk di `storage/app/backups`; data kembali utuh setelah restore |
| E6 | Backup otomatis | `php artisan schedule:work`, periksa `php artisan schedule:list` | Perintah `sipat:backup` terjadwal harian 21:00 Asia/Jakarta |
| E7 | Performa | Buka `/` dan `/daftar` dengan koneksi klinik | Halaman dimuat < 3 detik (aset produksi `npm run build`) |
| E8 | Mobile | Buka `/`, `/daftar`, `/status` di layar ponsel | Tampilan rapi tanpa scroll horizontal |

Bukti otomatis: `PerformanceTest`, `BackupTest`, `FilamentPanelTest`, `DashboardTest`.

## F. Indikator keberhasilan proyek (laporan)

| Kriteria | Metrik target | Cara verifikasi |
|----------|---------------|-----------------|
| Fungsionalitas sistem | ≥ 80% fitur utama berfungsi | Checklist A–E di atas + `php artisan test` |
| Pendaftaran online pasien umum | Pasien umum dapat daftar online | Skenario A1–A5 |
| Sinkronisasi antrian | Tidak ada nomor tumpang tindih | Skenario B3 |
| Waktu tunggu pasien umum | < 30 menit | Observasi lapangan dengan estimasi dari sistem |
| Kepuasan pengguna | ≥ 80% puas | Kuesioner UAT |
| Adopsi sistem | ≥ 70% staf aktif | Log penggunaan panel |
| Ketepatan waktu & anggaran | Deviasi ≤ 5% / ≤ 10% | Monitoring proyek |
