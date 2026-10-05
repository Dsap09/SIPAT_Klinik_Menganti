<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilPasienTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_dashboard_menampilkan_nomor_rm_dan_riwayat_sendiri(): void
    {
        $budi = $this->buatPasien(['Nama_Lengkap' => 'Budi Santoso', 'No_RM' => 'RM-2026-0001']);
        $siti = $this->buatPasien(['Nama_Lengkap' => 'Siti Aminah', 'No_RM' => 'RM-2026-0002']);

        $this->daftarkanAntrean($budi);
        $this->daftarkanAntrean($siti, 'JDW-02');

        $this->actingAs($budi, 'pasien');

        $this->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('RM-2026-0001')
            ->assertSee('Budi Santoso');

        $this->get(route('pasien.riwayat'))
            ->assertOk()
            ->assertSee('A-001')
            ->assertDontSee('A-002');
    }

    public function test_profil_dapat_diperbarui_dan_tercatat_di_audit_trail(): void
    {
        $pasien = $this->buatPasien(['Nama_Lengkap' => 'Budi Santoso']);

        $this->actingAs($pasien, 'pasien');

        $this->put(route('pasien.profil.simpan'), [
            'No_Telepon' => '0812-9999-8888',
            'Alamat' => 'Jl. Baru No. 2',
            'Agama' => 'Islam',
            'Pekerjaan' => 'Karyawan',
            'Status_Pernikahan' => 'Menikah',
            'Pendidikan' => 'S1',
            'Penanggung_Jawab' => 'Keluarga Budi',
        ])->assertRedirect(route('pasien.profil'));

        $pasien->refresh();

        $this->assertSame('081299998888', $pasien->No_Telepon);
        $this->assertSame('Jl. Baru No. 2', $pasien->Alamat);

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Pasien',
            'Deskripsi_Aksi' => 'Ubah data pasien: Budi Santoso',
        ]);

        $this->assertSame(
            1,
            AuditTrail::where('Entitas_Terdampak', 'Pasien')
                ->where('Deskripsi_Aksi', 'Ubah data pasien: Budi Santoso')
                ->count()
        );
    }

    public function test_profil_menolak_nomor_telepon_yang_sudah_dipakai(): void
    {
        $budi = $this->buatPasien(['Nama_Lengkap' => 'Budi Santoso', 'No_Telepon' => '081200000001']);
        $this->buatPasien(['Nama_Lengkap' => 'Siti Aminah', 'No_Telepon' => '081200000002']);

        $this->actingAs($budi, 'pasien');

        $this->put(route('pasien.profil.simpan'), [
            'No_Telepon' => '081200000002',
            'Alamat' => 'Jl. Baru No. 2',
        ])->assertSessionHasErrors('No_Telepon');

        $this->assertSame('081200000001', $budi->refresh()->No_Telepon);
    }

    public function test_data_identitas_tidak_dapat_diubah_dari_profil(): void
    {
        $pasien = $this->buatPasien([
            'Nama_Lengkap' => 'Budi Santoso',
            'NIK' => '3525123456780001',
            'Tgl_Lahir' => '1985-02-20',
        ]);

        $this->actingAs($pasien, 'pasien');

        $this->put(route('pasien.profil.simpan'), [
            'Nama_Lengkap' => 'Nama Berubah',
            'NIK' => '9999999999999999',
            'Tgl_Lahir' => '2000-01-01',
            'No_Telepon' => '081200000009',
            'Alamat' => 'Jl. Tetap',
        ])->assertRedirect(route('pasien.profil'));

        $pasien->refresh();

        $this->assertSame('Budi Santoso', $pasien->Nama_Lengkap);
        $this->assertSame('3525123456780001', $pasien->NIK);
        $this->assertSame('1985-02-20', $pasien->Tgl_Lahir->toDateString());
        $this->assertSame('081200000009', $pasien->No_Telepon);
    }

    public function test_pasien_tidak_bisa_melihat_riwayat_pasien_lain(): void
    {
        $budi = $this->buatPasien(['Nama_Lengkap' => 'Budi Santoso']);
        $siti = $this->buatPasien(['Nama_Lengkap' => 'Siti Aminah']);

        $this->daftarkanAntrean($siti);

        $this->actingAs($budi, 'pasien');

        $this->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertDontSee('Siti Aminah');

        $this->get(route('pasien.riwayat'))
            ->assertOk()
            ->assertDontSee('A-001');
    }
}
