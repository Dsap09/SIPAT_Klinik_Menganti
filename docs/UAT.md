# Panduan Pengujian & UAT — SIPAT Klinik Menganti

Checklist Black Box + User Acceptance Testing (minggu 13-14). Diturunkan dari `Product_Backlog_Kelompok4.xlsx` dan indikator keberhasilan pada `Laporan_Proyek_SIPAT_Klinik_Menganti.docx`.

## Persiapan

1. `php artisan migrate:fresh --seed` untuk menyiapkan basis data uji.
2. `npm run build` lalu `php artisan serve`.
3. (Opsional) `php artisan schedule:work` untuk menguji backup otomatis harian.
4. `php artisan test` — menjalankan seluruh pengujian otomatis (Black Box fungsional).

Akun uji: `admin`, `petugas`, `manajemen`, `dokter` — password `password`.

## A. Akun Pasien & Pendaftaran Berobat

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| A1 | Gerbang akun | Buka `/daftar` tanpa login | Muncul halaman gerbang akun (Masuk / Daftar Akun Baru); alur tamu tidak ada lagi |
| A2 | Registrasi akun pasien baru | Buka `/pasien/register`, isi data wajib (nama, tempat/tanggal lahir, jenis kelamin, NIK 16 digit, alamat, nomor HP, jenis pembayaran), submit | Nomor RM terbit otomatis `RM-{tahun}-0001`; pasien langsung masuk dashboard dan nomor RM tampil |
| A3 | Nomor RM berurutan | Daftarkan dua akun berturut-turut | Nomor RM `...-0001` dan `...-0002`; tidak ada duplikat |
| A4 | NIK / nomor HP ganda | Daftar dengan NIK atau nomor HP yang sudah terpakai | Validasi menolak (unik) |
| A5 | Login 2 dari 3 | Login dengan No RM + tanggal lahir; ulangi dengan nomor HP + tanggal lahir, dan No RM + nomor HP | Ketiganya berhasil masuk dashboard |
| A6 | Login tidak valid | Isi hanya 1 data, atau tanggal lahir salah | Ditolak; akun tidak masuk |
| A7 | Daftar berobat tanpa isi ulang | Dari dashboard klik "Daftar Berobat", pilih poli/jadwal, submit | Nomor `A-001` + estimasi terbit; data pasien tidak diisi ulang dan kuota berkurang 1 |
| A8 | Kuota habis | Habiskan kuota sebuah jadwal, lalu daftar ke jadwal itu | Pendaftaran ditolak: "Kuota jadwal ini sudah habis" |
| A9 | Pasien BPJS | Akun dengan jenis pembayaran BPJS membuka "Daftar Berobat" | Diarahkan ke dashboard + info Mobile JKN; tidak ada antrean online yang terbit |
| A10 | Riwayat kunjungan | Buka `/pasien/riwayat` | Hanya antrean milik pasien yang tampil, dengan status dan tautan detail |
| A11 | Profil pasien | Ubah nomor HP/alamat/data sosial di `/pasien/profil`, submit | Perubahan tersimpan dan tercatat di `AUDIT_TRAIL`; NIK/nama/tanggal lahir tidak dapat diubah sendiri |
| A12 | Estimasi waktu | Lihat halaman status setelah daftar | Estimasi tampil (jam mulai + 15 menit per pasien, dibatasi jam selesai) |

Bukti otomatis: `AkunPasienTest`, `ProfilPasienTest`, `RekamMedisServiceTest`, `PendaftaranTest`.

## B. Antrian Terpadu (BPJS + Umum)

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| B1 | Input BPJS manual | Panel `/panel` → Loket → Antrean → Input Pasien, jenis BPJS, isi No BPJS | Nomor RM terbit otomatis (mis. `RM-2026-0001`) dan nomor antrean pada urutan tunggal (melanjutkan nomor umum) |
| B2 | Walk-in umum | Sama, jenis "Umum (walk-in)", isi nomor HP opsional | Nomor RM terbit, nomor antrean terbit, `No_BPJS` kosong |
| B3 | Tidak ada duplikat | Daftarkan 20+ pasien campuran umum/BPJS dalam satu hari, lalu periksa daftar antrean | Tidak ada nomor yang sama pada tanggal yang sama; urutan berurutan `A-001`, `A-002`, ... |
| B4 | Kuota turun sesuai | Bandingkan "Sisa Kuota" di Data Master dengan jumlah antrean aktif | Sisa = kuota maksimal − antrean non-Batal |

Bukti otomatis: `AuditAntreanTest::test_tidak_ada_nomor_antrean_duplikat_atau_tumpang_tindih_dalam_sehari`.

## C. Check-in dan Kartu Antrian

| No | Skenario | Langkah | Hasil yang diharapkan |
|----|----------|---------|------------------------|
| C1 | Verifikasi cocok | Klik Check-in, masukkan No RM pasien yang benar (huruf besar/kecil bebas) | Check-in tersimpan, status tetap Menunggu, kartu antrian terbuka untuk dicetak |
| C2 | Verifikasi gagal | Klik Check-in, masukkan No RM yang salah | Check-in ditolak, status tetap Menunggu, tidak ada audit tercatat |
| C3 | Kartu | Klik tombol Kartu | Kartu memuat nomor, nama, poli, dokter, jadwal, dan waktu check-in |
| C4 | Ubah status | Ubah Status: Menunggu → Dilayani → Selesai | Daftar antrean, halaman status pasien, dan dashboard mengikuti status terbaru |
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
| E4 | Rate limit | Kirim >20 registrasi akun/menit atau >10 login pasien/menit dari satu IP | Permintaan berikutnya dibalas `429 Too Many Requests` |
| E5 | Backup | `php artisan sipat:backup` lalu `php artisan sipat:restore <file> --force` | File `.sql` terbentuk di `storage/app/backups`; data kembali utuh setelah restore |
| E6 | Backup otomatis | `php artisan schedule:work`, periksa `php artisan schedule:list` | Perintah `sipat:backup` terjadwal harian 21:00 Asia/Jakarta |
| E7 | Performa | Buka `/`, `/daftar`, `/pasien/login`, dan `/pasien/register` dengan koneksi klinik | Halaman dimuat < 3 detik (aset produksi `npm run build`) |
| E8 | Mobile | Buka `/`, `/daftar`, `/pasien/login`, `/pasien/register`, `/status` di layar ponsel | Tampilan rapi tanpa scroll horizontal |

Bukti otomatis: `PerformanceTest`, `BackupTest`, `FilamentPanelTest`, `DashboardTest`.

## F. Indikator keberhasilan proyek (laporan)

| Kriteria | Metrik target | Cara verifikasi |
|----------|---------------|-----------------|
| Fungsionalitas sistem | ≥ 80% fitur utama berfungsi | Checklist A–E di atas + `php artisan test` |
| Pendaftaran online pasien umum | Pasien umum dapat daftar online | Skenario A1–A9 |
| Akun pasien & nomor RM otomatis | 100% fitur akun berfungsi; 0 duplikasi nomor RM | Skenario A1–A11 + `RekamMedisServiceTest` |
| Login pasien lama | Pasien lama dapat daftar berobat tanpa isi ulang data | Skenario A5–A7 |
| Sinkronisasi antrian | Tidak ada nomor tumpang tindih | Skenario B3 |
| Waktu tunggu pasien umum | < 30 menit | Observasi lapangan dengan estimasi dari sistem |
| Kepuasan pengguna | ≥ 80% puas | Kuesioner UAT |
| Adopsi sistem | ≥ 70% staf aktif | Log penggunaan panel |
| Ketepatan waktu & anggaran | Deviasi ≤ 5% / ≤ 10% | Monitoring proyek |
