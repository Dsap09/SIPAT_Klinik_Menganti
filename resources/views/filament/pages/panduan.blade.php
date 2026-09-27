<x-filament-panels::page>
    <x-filament::section
        heading="Panduan Singkat Pengguna SIPAT"
        description="Satu halaman — cukup untuk mulai bekerja tanpa pelatihan ulang."
    >
        <p>
            SIPAT dipakai dari panel staf di <strong>/panel</strong>. Masuk memakai <strong>username</strong> dan
            <strong>password</strong> staf. Setelah masuk, menu di sisi kiri menyesuaikan peran Anda.
        </p>
    </x-filament::section>

    <x-filament::section heading="Peran pengguna" collapsible collapsed>
        <p><strong>Admin</strong> — mengelola data master (Poli, Dokter, Jadwal Layanan) dan melihat dashboard.</p>
        <p><strong>Petugas</strong> — bekerja di menu Loket: input pasien BPJS/walk-in, check-in, cetak kartu, ubah status antrean.</p>
        <p><strong>Manajemen</strong> — melihat dashboard ringkasan antrean hari ini.</p>
        <p><strong>Dokter</strong> — melihat dashboard; pengelolaan antrean dilakukan oleh Petugas/Admin.</p>
    </x-filament::section>

    <x-filament::section heading="1. Menyiapkan data master (Admin)" collapsible collapsed>
        <p>Urutan pengerjaan: <strong>Poli</strong> &rarr; <strong>Dokter</strong> &rarr; <strong>Jadwal Layanan</strong>.</p>
        <p>Buka menu <strong>Data Master</strong>, klik <strong>New</strong>, isi kolom, lalu <strong>Create</strong>. ID (mis. <code>POLI-03</code>) dibuat otomatis.</p>
        <p>Pada Jadwal Layanan, isi hari, jam mulai/selesai, dan <strong>kuota maksimal</strong>. Kolom <em>Sisa Kuota</em> boleh dikosongkan — otomatis mengikuti kuota maksimal. Perubahan kuota langsung berlaku untuk pendaftaran berikutnya.</p>
    </x-filament::section>

    <x-filament::section heading="2. Melayani pasien di loket (Petugas)" collapsible collapsed>
        <p><strong>a. Input pasien BPJS atau walk-in.</strong> Buka <strong>Loket &rarr; Antrean</strong>, klik <strong>Input Pasien (BPJS / Walk-in)</strong>. Pilih jadwal, pilih jenis pasien (BPJS wajib mengisi nomor BPJS), isi nama dan tanggal lahir, lalu <strong>Create</strong>. Sistem memberi nomor antrean terpadu (mis. <code>A-001</code>) dan langsung membuka kartu antrian untuk dicetak.</p>
        <p><strong>b. Check-in dan verifikasi.</strong> Pada baris antrean klik <strong>Check-in</strong>, masukkan <strong>tanggal lahir</strong> pasien. Jika cocok, check-in tersimpan dan kartu antrian otomatis terbuka untuk dicetak. Jika tidak cocok, check-in ditolak.</p>
        <p><strong>c. Mengubah status.</strong> Klik <strong>Ubah Status</strong>: <em>Menunggu</em> &rarr; <em>Dilayani</em> &rarr; <em>Selesai</em>, atau <em>Batal</em> bila pasien tidak hadir.</p>
        <p><strong>d. Cetak ulang kartu.</strong> Klik tombol <strong>Kartu</strong> pada antrean yang sudah check-in.</p>
        <p>Daftar antrean hanya menampilkan <strong>antrean hari ini</strong>. Gunakan filter <em>Status</em> untuk menyaring.</p>
    </x-filament::section>

    <x-filament::section heading="3. Dashboard (Admin &amp; Manajemen)" collapsible collapsed>
        <p>Menu <strong>Dashboard</strong> menampilkan total pasien hari ini, jumlah pasien <strong>BPJS</strong> dan <strong>Umum</strong>, serta jumlah antrean per status (Menunggu, Dilayani, Selesai, Batal, Sudah Check-in).</p>
        <p>Angka di dashboard bersumber dari basis data yang sama dengan daftar antrean.</p>
    </x-filament::section>

    <x-filament::section heading="4. Sisi pasien (online)" collapsible collapsed>
        <p>Pasien umum mendaftar sendiri di halaman <strong>/daftar</strong>: pasien baru mengisi data diri, pasien lama memasukkan No RM. Setelah mendaftar, pasien menerima nomor antrean dan estimasi waktu kedatangan.</p>
        <p>Di halaman <strong>/status/{nomor}</strong>, pasien melihat status terbaru (diperbarui otomatis tiap 30 detik), dapat <strong>membatalkan</strong> atau <strong>menjadwalkan ulang</strong> selama belum check-in. Kuota yang ditinggalkan otomatis dikembalikan.</p>
    </x-filament::section>

    <x-filament::section heading="Hal yang sering ditanyakan" collapsible collapsed>
        <p><strong>Jadwal tidak muncul saat input pasien?</strong> Kuota jadwal tersebut sudah habis atau belum diisi. Periksa menu Jadwal Layanan.</p>
        <p><strong>Nomor antrean tidak berurutan dengan poli lain?</strong> Itu benar: BPJS dan umum memakai satu nomor urut terpadu per hari.</p>
        <p><strong>Data aman?</strong> Setiap perubahan data pasien dan aksi antrean tercatat di Audit Trail, dan basis data dibackup otomatis setiap hari pukul 21.00 WIB (7 file terakhir disimpan).</p>
        <p><strong>Butuh bantuan lain?</strong> Hubungi Admin klinik.</p>
    </x-filament::section>
</x-filament-panels::page>
