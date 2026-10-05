# AGENTS.md

SIPAT (Sistem Antrian Online Terpadu) — Klinik Menganti. University IT-project-management coursework: a web-based clinic queue/registration system that unifies BPJS and general ("umum") patients into one fair queue.

## Current state
- All 5 sprints implemented. Laravel 13 app at the repo root: atomic single queue counter (`A-001`), estimated arrival time, staff auth + RBAC, manual BPJS/walk-in entry, check-in verification + printable queue card, audit trail, cancel/reschedule with quota release, status polling, daily DB backup/restore, and a Filament v5 back-office (master data, dashboard, in-panel user guide).
- Post-MVP patient accounts: self-registration issues an automatic medical record number (`RM-{TAHUN}-{URUTAN}`), passwordless login via "2 of 3" (No RM / phone / birth date) on a dedicated `pasien` guard, patient dashboard/history/profile, and account-gated online booking. Legacy patient/queue data was purged by migration to standardise on the new RM format.
- UAT/Black Box checklist lives in `docs/UAT.md`; no remaining backlog items for v1.0 MVP.
- Product backlog `Product_Backlog_Kelompok4.xlsx` stays the source of truth for work tracking. Execution plan lives in `docs/PLAN.md`.

## References (docs/)
- `Laporan_Proyek_SIPAT_Klinik_Menganti.docx` — problem statement, stakeholder/pain-point analysis, solution, in/out-of-scope, timeline, team, success metrics, business case.
- `Product_Backlog_Kelompok4.xlsx` — sprint backlog, the source of truth for work tracking. Structure: THEME > EPIC > USER STORY > ACCEPTANCE CRITERIA. Columns: Sprint (1-5), Progress (To Do/In Progress/Done/Blocked), Versi (baseline v1.0 MVP), PIC, Priority (MoSCoW), PIC Status, Tested Design, Tested Code.
- `ERD.png` — database schema (see "Data model" below).
- `flowchat sipat.png` — end-to-end system flow (see "System flow" below).
- `PLAN.md` — sprint execution plan + status per sprint.
- `UAT.md` — Black Box + UAT checklist, mapped to the backlog acceptance criteria and the report's success metrics.

## Stack
Laravel 13 (PHP 8.4) + MySQL database `sipat_klinik_menganti`. Patient-facing pages use Bootstrap 5 via Vite; the staff back-office ("panel") is **Filament v5** at `/panel` (Livewire 4, ships its own Tailwind-based assets). Laravel's default Tailwind scaffold was replaced with Bootstrap — do not use Tailwind for app pages; Filament's styles stay scoped to the panel.

## Commands
- Prerequisite: PHP `ext-zip` must be enabled (Filament dependency).
- Setup: `composer install` → `npm install` → `php artisan key:generate` → `php artisan migrate --seed`
- Dev: `php artisan serve` + `npm run dev` (or `npm run build` for production assets)
- Verify: `php artisan test` (feature tests) and `vendor/bin/pint` (formatting)
- If the panel renders unstyled, re-publish its assets: `php artisan filament:assets`
- Backup: `php artisan sipat:backup` (scheduled daily 21:00 `Asia/Jakarta`, keeps 7 files in `storage/app/backups`). Restore: `php artisan sipat:restore {file} --force`. Run the scheduler locally with `php artisan schedule:work`.
- Tests run on SQLite `:memory:` (`phpunit.xml`) while the app uses MySQL — keep migrations portable. Filament page/action tests need `Filament::setCurrentPanel(Filament::getPanel('panel'))` in `setUp()`.

## Core modules
1. **Pendaftaran Online Pasien Umum** — an account is required: new patients self-register at `/pasien/register` (auto RM number), returning patients log in at `/pasien/login` (2 of 3 verification). Booking `/pasien/daftar` reuses the stored profile and returns queue number + estimated arrival time. `/daftar` is now just the account gate; the old guest flow is gone.
2. **Antrian Terpadu** — one single queue counter for BPJS + umum. Staff manually enter BPJS bookings from Mobile JKN (no API integration). Check-in = verify the patient's No RM (case-insensitive) and print queue card; status stays `Menunggu` until staff change it manually (`Dilayani` → `Selesai`).
3. **Dashboard** — today's patient count, queue status, daily recap.
Supporting: data master (poli/dokter/jadwal + quotas), notifications, RBAC + audit trail, daily DB backup, mobile-responsive pages.

## Data model (docs/ERD.png)
PK/FK are `string` named in Indonesian — keep exact names/values. 7 tables:
- `POLI` (ID_Poli PK, Nama_Poli, Deskripsi)
- `DOKTER` (ID_Dokter PK, Nama_Dokter, Spesialisasi, ID_Poli FK)
- `JADWAL` (ID_Jadwal PK, Hari_Layanan, Jam_Mulai, Jam_Selesai, Kuota_Maksimal, Sisa_Kuota, ID_Poli FK, ID_Dokter FK)
- `PASIEN` (ID_Pasien PK, No_RM unique — always issued as `RM-{TAHUN}-{URUTAN}`, NIK unique, Nama_Lengkap, Tempat_Lahir, Tgl_Lahir, Jenis_Kelamin, Alamat, No_Telepon unique, Jenis_Pasien = `UMUM`|`BPJS`, No_BPJS — empty when UMUM, Agama, Pekerjaan, Status_Pernikahan, Pendidikan, Penanggung_Jawab). Account columns are nullable in the migration so pre-existing rows stay valid.
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
- Online **booking** is for `Jenis_Pasien = UMUM` only (BPJS patients are redirected to Mobile JKN at the counter); BPJS + walk-in umum are entered by staff from the Filament panel (`/panel/antreans/create`). Both paths still route through `QueueService`, and both issue an RM via `App\Services\RekamMedisService::nomorBaru()` (`RM-{TAHUN}-{URUTAN 4 digit}`, `Cache::lock` + transaction, yearly reset, unique-index backstop).
- Patient auth uses the **`pasien` guard** (session, provider `pasien` → `Pasien` model, which extends `Authenticatable` and returns `''` from `getRememberTokenName()`). There is **no password**: `/pasien/login` accepts at least 2 of {`No_RM`, `No_Telepon`, `Tgl_Lahir`} and logs in with `auth('pasien')->login($pasien)`. Phone numbers are normalised with `App\Support\NomorTelepon::normalisasi()` before store/lookup. Do not add patient credentials to `PENGGUNA` or the unused `users` table. Guests hitting `pasien/*` are redirected to `pasien.login` by the request-aware `redirectGuestsTo` in `bootstrap/app.php`.
- Staff auth uses `PENGGUNA` (provider `pengguna`). Login is Filament's page, overridden by `App\Filament\Pages\Auth\Login` to use `Username` instead of `email`. For programmatic attempts use `Auth::attempt(['Username' => $u, 'password' => $p])` — the credential key must be lowercase `password` (`EloquentUserProvider` filters keys containing "password" case-sensitively), while `Pengguna::getAuthPasswordName()` returns `'Password'`. Seeded accounts: `admin`, `petugas`, `manajemen`, `dokter` / `password`.
- `PENGGUNA` has **no `remember_token` column** (not in the ERD): the Filament login omits the remember-me checkbox and `Pengguna::getRememberTokenName()` returns `''` so Laravel never writes that column. Do not re-add a remember-me field unless you add the column.
- `sessions.user_id` is deliberately `varchar(36)` (migration `2026_09_28_000002`): `ID_Pengguna` is a string (`USR-01`), and Laravel's default `bigint unsigned` makes MySQL reject the authenticated session write under `STRICT_TRANS_TABLES`, so logins silently never persist. SQLite tests cannot catch this — do not revert the column type.
- Panel authorization: `Pengguna::canAccessPanel()` allows all 4 roles; each Filament resource gates access with `canAccess()` (master data = Admin only, antrean = Petugas+Admin). The `role` middleware alias still guards the plain-Blade queue card route `/kartu/{noAntrean}`.
- Filament resources handle the string PKs fine; new records get their id from `App\Support\KodeGenerator` inside each `Create*` page's `mutateFormDataBeforeCreate`.
- Patient-data changes are audited automatically via `#[ObservedBy([PasienObserver::class])]` + `App\Services\AuditLogger`; log queue actions explicitly with `AuditLogger::catat('Antrean', ...)`. `AuditLogger` reads `Auth::guard('web')->id()` on purpose — never `Auth::id()`, which follows the default guard and can pick up a patient session. `ID_Pengguna` is null for patient/guest actions (the FK points to `PENGGUNA` only).
- Queue estimation (backlog 1.1.2) lives in `QueueService::estimasi()` — `Jam_Mulai` + 15 min per queued patient, clamped to `Jam_Selesai`. Dashboard numbers come from `App\Services\DashboardMetrics` (single source for the widget + tests).
- `ANTREAN.Tanggal_Kunjungan` is a DATE column but Eloquent stores it as `Y-m-d H:i:s`, so query it with the `Antrean::padaTanggal()` scope (BETWEEN `00:00:00`/`23:59:59`). A plain `where('Tanggal_Kunjungan', 'Y-m-d')` silently matches nothing on SQLite.
- `APP_TIMEZONE=Asia/Jakarta` feeds `config('app.timezone')`; the scheduler and backup times use it.
- Patient status "notifications" are poll-based: the status page hits `GET /status/{noAntrean}/data` every 30s and shows a banner when the status changes. There is no email/WhatsApp channel (out of scope).
- Cancel/reschedule must go through `QueueService::batalkan()` / `jadwalkanUlang()` so quota is released with row locks; only `Menunggu` + not-checked-in antrean (see `Antrean::bisaDibatalkan()`) can be cancelled.
- Public POST/JSON endpoints are rate limited via named limiters in `AppServiceProvider` (`config/sipat.php` → `THROTTLE_PENDAFTARAN`, `THROTTLE_STATUS`, `THROTTLE_PASIEN_LOGIN`). Tests override `config('sipat.throttle.*')` to keep limits low.
- Migration `2026_10_05_000002_bersihkan_data_pasien_lama` deletes **all** `ANTREAN` + `PASIEN` rows and resets `JADWAL.Sisa_Kuota`; its `down()` is a no-op (irreversible by design). Patient/queue test fixtures use the `buatPasien()` / `daftarkanAntrean()` / `registrasiAkun()` helpers in `Tests\TestCase`.
- The in-panel user guide is `App\Filament\Pages\Panduan` (menu "Bantuan"), view `resources/views/filament/pages/panduan.blade.php` — update it when user-facing flows change.
- Clinic profile/contact (address, phone, hours, map) lives in **`config/klinik.php`**, not in views. Values are still placeholders; while `contoh => true` the footer shows a "not yet verified" note. Replace the values (and flip the flag) once the clinic confirms them.
- Icons use the anonymous component `resources/views/components/si-icon.blade.php` → `<x-si-icon name="clock" />`. Do **not** rename it to `icon`: Filament pulls in `blade-ui-kit/blade-icons`, which already registers `<x-icon>` and wins resolution (`SvgNotFound` at render time).
- Patient pages share one design system in `resources/css/app.css` (teal palette, Bootstrap primary overridden through CSS variables — no Tailwind). Landing page data (poli/jadwal) is loaded in `PendaftaranController::beranda()`; keep it eager-loaded because `PerformanceTest` caps `/`, `/daftar`, `/pasien/login`, and `/pasien/register` at 15 queries and 3 seconds. `Paginator::useBootstrapFive()` is set in `AppServiceProvider` — keep it for the patient history table.
- Backlog `Petunjuk` sheet maps PICs differently than the report's team table (backlog: Faris=PO, Adolfani=Scrum Master, Sendy=Designer, Doni=Developer, Rafi=Tester). Trust the backlog sheet for sprint assignments.
- Testing = Black Box + UAT (weeks 13-14). Acceptance criteria that must hold: no duplicate/overlapping queue numbers in a 1-day audit, dashboard numbers exactly match DB, notification ≤60s after status change, home page loads ≤3s, mobile pages have no horizontal scroll.