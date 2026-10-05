<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunPasienTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_register_dan_login_dapat_diakses(): void
    {
        $this->get(route('pasien.register'))
            ->assertOk()
            ->assertSee('Pendaftaran Akun');

        $this->get(route('pasien.login'))
            ->assertOk()
            ->assertSee('Masuk untuk Daftar Berobat');
    }

    public function test_registrasi_menerbitkan_nomor_rm_dan_langsung_masuk_dashboard(): void
    {
        $this->registrasiAkun(['Nama_Lengkap' => 'Budi Santoso', 'No_Telepon' => '081234567890']);

        $nomorRm = 'RM-'.now()->year.'-0001';

        $this->assertDatabaseHas('PASIEN', [
            'Nama_Lengkap' => 'Budi Santoso',
            'No_RM' => $nomorRm,
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ]);

        $this->assertAuthenticated('pasien');

        $this->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee($nomorRm)
            ->assertSee('Budi Santoso');
    }

    public function test_nomor_rm_berurutan_tanpa_duplikat(): void
    {
        $this->registrasiAkun(['Nama_Lengkap' => 'Pasien A', 'No_Telepon' => '081200000001']);
        $this->registrasiAkun(['Nama_Lengkap' => 'Pasien B', 'No_Telepon' => '081200000002']);

        $tahun = now()->year;

        $this->assertDatabaseHas('PASIEN', ['No_RM' => "RM-{$tahun}-0001"]);
        $this->assertDatabaseHas('PASIEN', ['No_RM' => "RM-{$tahun}-0002"]);
        $this->assertSame(2, Pasien::query()->distinct()->count('No_RM'));
    }

    public function test_nik_duplikat_ditolak(): void
    {
        $this->registrasiAkun(['NIK' => '3525123456780001', 'No_Telepon' => '081200000001']);

        $this->post(route('pasien.register.simpan'), [
            'Nama_Lengkap' => 'Pasien Lain',
            'Tempat_Lahir' => 'Gresik',
            'Tgl_Lahir' => '1991-01-01',
            'Jenis_Kelamin' => 'Perempuan',
            'NIK' => '3525123456780001',
            'Alamat' => 'Jl. Lain',
            'No_Telepon' => '081200000002',
            'Jenis_Pasien' => 'UMUM',
        ])->assertSessionHasErrors('NIK');

        $this->assertSame(1, Pasien::count());
    }

    public function test_nomor_telepon_duplikat_ditolak(): void
    {
        $this->registrasiAkun(['No_Telepon' => '081200000001']);

        $this->post(route('pasien.register.simpan'), [
            'Nama_Lengkap' => 'Pasien Lain',
            'Tempat_Lahir' => 'Gresik',
            'Tgl_Lahir' => '1991-01-01',
            'Jenis_Kelamin' => 'Perempuan',
            'NIK' => '3525123456780002',
            'Alamat' => 'Jl. Lain',
            'No_Telepon' => '0812-0000-0001',
            'Jenis_Pasien' => 'UMUM',
        ])->assertSessionHasErrors('No_Telepon');

        $this->assertSame(1, Pasien::count());
    }

    public function test_validasi_data_wajib_registrasi(): void
    {
        $this->post(route('pasien.register.simpan'), [])
            ->assertSessionHasErrors([
                'Nama_Lengkap',
                'Tempat_Lahir',
                'Tgl_Lahir',
                'Jenis_Kelamin',
                'NIK',
                'Alamat',
                'No_Telepon',
                'Jenis_Pasien',
            ]);

        $this->assertSame(0, Pasien::count());
    }

    public function test_login_dengan_rm_dan_tanggal_lahir(): void
    {
        $pasien = $this->buatPasien(['No_RM' => 'RM-2026-0042', 'Tgl_Lahir' => '1985-02-20']);

        $this->post(route('pasien.login.proses'), [
            'No_RM' => $pasien->No_RM,
            'Tgl_Lahir' => '1985-02-20',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->assertAuthenticatedAs($pasien, 'pasien');
    }

    public function test_login_dengan_telepon_dan_tanggal_lahir(): void
    {
        $pasien = $this->buatPasien(['No_Telepon' => '081234567890', 'Tgl_Lahir' => '1985-02-20']);

        $this->post(route('pasien.login.proses'), [
            'No_Telepon' => '0812-3456-7890',
            'Tgl_Lahir' => '1985-02-20',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->assertAuthenticatedAs($pasien, 'pasien');
    }

    public function test_login_dengan_rm_dan_nomor_telepon(): void
    {
        $pasien = $this->buatPasien(['No_RM' => 'RM-2026-0042', 'No_Telepon' => '081234567890']);

        $this->post(route('pasien.login.proses'), [
            'No_RM' => $pasien->No_RM,
            'No_Telepon' => '081234567890',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->assertAuthenticatedAs($pasien, 'pasien');
    }

    public function test_login_kurang_dari_dua_data_ditolak(): void
    {
        $this->post(route('pasien.login.proses'), ['No_RM' => 'RM-2026-0042'])
            ->assertSessionHasErrors('No_RM');

        $this->assertGuest('pasien');
    }

    public function test_login_dengan_data_tidak_cocok_ditolak(): void
    {
        $this->buatPasien(['No_RM' => 'RM-2026-0042', 'Tgl_Lahir' => '1985-02-20']);

        $this->post(route('pasien.login.proses'), [
            'No_RM' => 'RM-2026-0042',
            'Tgl_Lahir' => '1986-01-01',
        ])->assertSessionHasErrors('No_RM');

        $this->assertGuest('pasien');
    }

    public function test_tamu_tidak_bisa_membuka_halaman_pasien(): void
    {
        $this->get(route('pasien.dashboard'))->assertRedirect(route('pasien.login'));
        $this->get(route('pasien.riwayat'))->assertRedirect(route('pasien.login'));
        $this->get(route('pasien.profil'))->assertRedirect(route('pasien.login'));
        $this->get(route('pasien.daftar'))->assertRedirect(route('pasien.login'));
    }

    public function test_pasien_bisa_keluar(): void
    {
        $pasien = $this->buatPasien();

        $this->actingAs($pasien, 'pasien');

        $this->post(route('pasien.logout'))
            ->assertRedirect(route('beranda'));

        $this->assertGuest('pasien');
    }

    public function test_pasien_umum_bisa_daftar_berobat_tanpa_isi_data_ulang(): void
    {
        $pasien = $this->registrasiAkun(['Nama_Lengkap' => 'Siti Aminah']);

        $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
            ->assertRedirect(route('pendaftaran.status', 'A-001'));

        $this->assertDatabaseHas('ANTREAN', [
            'No_Antrean' => 'A-001',
            'ID_Pasien' => $pasien->ID_Pasien,
            'Status' => Antrean::STATUS_MENUNGGU,
        ]);

        $this->assertSame(1, Pasien::count());
        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_pasien_bpjs_tidak_bisa_daftar_antrean_online(): void
    {
        $this->registrasiAkun([
            'Jenis_Pasien' => 'BPJS',
            'No_BPJS' => '0001234567890',
        ]);

        $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
            ->assertRedirect(route('pasien.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('ANTREAN', 0);
        $this->assertSame(20, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_registrasi_dibatasi_setelah_batas_per_menit(): void
    {
        config()->set('sipat.throttle.pendaftaran', 1);

        $this->registrasiAkun(['No_Telepon' => '081200000001']);

        $this->post(route('pasien.register.simpan'), [
            'Nama_Lengkap' => 'Pasien Lain',
            'Tempat_Lahir' => 'Gresik',
            'Tgl_Lahir' => '1991-01-01',
            'Jenis_Kelamin' => 'Perempuan',
            'NIK' => '3525123456780002',
            'Alamat' => 'Jl. Lain',
            'No_Telepon' => '081200000002',
            'Jenis_Pasien' => 'UMUM',
        ])->assertStatus(429);
    }

    public function test_login_dibatasi_setelah_batas_per_menit(): void
    {
        config()->set('sipat.throttle.login', 2);

        $this->buatPasien(['No_RM' => 'RM-2026-0042', 'Tgl_Lahir' => '1985-02-20']);

        for ($i = 0; $i < 2; $i++) {
            $this->post(route('pasien.login.proses'), [
                'No_RM' => 'RM-2026-0042',
                'Tgl_Lahir' => '1986-01-01',
            ])->assertSessionHasErrors('No_RM');
        }

        $this->post(route('pasien.login.proses'), [
            'No_RM' => 'RM-2026-0042',
            'Tgl_Lahir' => '1986-01-01',
        ])->assertStatus(429);
    }
}
