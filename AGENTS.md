# AGENTS.md

SIPAT (Sistem Antrian Online Terpadu) — Klinik Menganti. University IT-project-management coursework: a web-based clinic queue/registration system that unifies BPJS and general ("umum") patients into one fair queue.

## Current state
- Laravel 13 app scaffolded at the repo root. Sprint 1 implemented: public online registration for umum patients, atomic single queue counter (`A-001`), responsive Bootstrap pages.
- Sprint 2-5 (BPJS manual entry, check-in + queue card, dashboard, master data, notifications, backup, RBAC/audit) are NOT built yet.
- Product backlog `Product_Backlog_Kelompok4.xlsx` stays the source of truth for work tracking. Execution plan lives in `docs/PLAN.md`.

## References (docs/)
- `Laporan_Proyek_SIPAT_Klinik_Menganti.docx` — problem statement, stakeholder/pain-point analysis, solution, in/out-of-scope, timeline, team, success metrics, business case.
- `Product_Backlog_Kelompok4.xlsx` — sprint backlog, the source of truth for work tracking. Structure: THEME > EPIC > USER STORY > ACCEPTANCE CRITERIA. Columns: Sprint (1-5), Progress (To Do/In Progress/Done/Blocked), Versi (baseline v1.0 MVP), PIC, Priority (MoSCoW), PIC Status, Tested Design, Tested Code.
- `ERD.png` — database schema (see "Data model" below).
- `flowchat sipat.png` — end-to-end system flow (see "System flow" below).

## Stack
Laravel 13 (PHP 8.4) + MySQL database `sipat_klinik_menganti` + Bootstrap 5 via Vite. Agile/Scrum, ~5 sprints over 16 weeks. Laravel's default Tailwind scaffold was replaced with Bootstrap — do not reintroduce Tailwind.

## Commands
- Setup: `composer install` → `npm install` → `php artisan key:generate` → `php artisan migrate --seed`
- Dev: `php artisan serve` + `npm run dev` (or `npm run build` for production assets)
- Verify: `php artisan test` (feature tests) and `vendor/bin/pint` (formatting)
- Tests run on SQLite `:memory:` (`phpunit.xml`) while the app uses MySQL — keep migrations portable.

## Core modules
1. **Pendaftaran Online Pasien Umum** — general patients register via web; new patients fill personal data, returning patients enter their No RM. Receives queue number + estimated arrival time.
2. **Antrian Terpadu** — one single queue counter for BPJS + umum. Staff manually enter BPJS bookings from Mobile JKN (no API integration). Check-in = verify name + date of birth, print queue card.
3. **Dashboard** — today's patient count, queue status, daily recap.
Supporting: data master (poli/dokter/jadwal + quotas), notifications, RBAC + audit trail, daily DB backup, mobile-responsive pages.

## Data model (docs/ERD.png)
PK/FK are `string` named in Indonesian — keep exact names/values. 7 tables:
- `POLI` (ID_Poli PK, Nama_Poli, Deskripsi)
- `DOKTER` (ID_Dokter PK, Nama_Dokter, Spesialisasi, ID_Poli FK)
- `JADWAL` (ID_Jadwal PK, Hari_Layanan, Jam_Mulai, Jam_Selesai, Kuota_Maksimal, Sisa_Kuota, ID_Poli FK, ID_Dokter FK)
- `PASIEN` (ID_Pasien PK, No_RM unique — empty for new patients, Nama_Lengkap, Tgl_Lahir, Alamat, Jenis_Pasien = `UMUM`|`BPJS`, No_BPJS — empty when UMUM)
- `ANTREAN` (ID_Antrean PK, No_Antrean e.g. `A-001`, Tanggal_Kunjungan, Estimasi_Waktu, Status = `Menunggu`|`Dilayani`|`Selesai`|`Batal`, Waktu_CheckIn, ID_Pasien FK, ID_Jadwal FK)
- `PENGGUNA` (ID_Pengguna PK, Username, Password (encrypted), Role = `Admin`|`Petugas`|`Manajemen`|`Dokter`, Nama_Pengguna)
- `AUDIT_TRAIL` (ID_Log PK, Waktu_Akses, Entitas_Terdampak, Deskripsi_Aksi, ID_Pengguna FK)

## System flow (docs/flowchat sipat.png)
Actors: **Admin Klinik** (kelola master poli/dokter/jadwal/kuota), **Sistem SIPAT** (single queue counter, hitung estimasi waktu, sinkronisasi kuota, simpan antrean, backup harian), **Pasien Umum & BPJS** (online: pilih poli/jadwal lalu dapat No RM + estimasi; atau walk-in), **Petugas Administrasi** (input data BPJS dari Mobile JKN, verifikasi/check-in, cetak kartu antrian), **Dokter** (melayani pasien), **Manajemen** (dashboard real-time). Status antrean pada flowchart selaras dengan enum `ANTREAN.Status` di ERD.

## Scope boundaries — do NOT implement
Out of scope per project charter: Mobile JKN API integration, drug stock management, complex billing, full SATUSEHAT integration, native mobile app, AI diagnosis.

## Compliance
PMK No. 24 Tahun 2022 (electronic medical records) drives security: role-based access + audit trail for patient data (backlog 6.1.1), automated daily backup (6.1.4).

## Conventions / gotchas
- All project docs are in **Indonesian**; write user-facing text and new docs in Indonesian.
- Models set `$table`, `$primaryKey`, `$keyType='string'`, `$incrementing=false`, `$timestamps=false` (the ERD has no timestamp columns). Use Laravel 13's `#[Fillable]` / `#[Hidden]` attributes.
- Queue numbers are per-day (`A-001`, zero-padded, one counter for BPJS + umum). Generate them only via `App\Services\QueueService` — it uses `Cache::lock` + a DB transaction, backed by the unique index `(Tanggal_Kunjungan, No_Antrean)`.
- Online registration is for `Jenis_Pasien = UMUM` only; BPJS is entered by staff (Sprint 2).
- Backlog `Petunjuk` sheet maps PICs differently than the report's team table (backlog: Faris=PO, Adolfani=Scrum Master, Sendy=Designer, Doni=Developer, Rafi=Tester). Trust the backlog sheet for sprint assignments.
- Testing = Black Box + UAT (weeks 13-14). Acceptance criteria that must hold: no duplicate/overlapping queue numbers in a 1-day audit, dashboard numbers exactly match DB, notification ≤60s after status change, home page loads ≤3s, mobile pages have no horizontal scroll.