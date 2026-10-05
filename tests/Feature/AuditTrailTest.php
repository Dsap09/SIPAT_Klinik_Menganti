<?php

namespace Tests\Feature;

use App\Filament\Resources\Antreans\Pages\CreateAntrean;
use App\Filament\Resources\Antreans\Pages\ListAntreans;
use App\Models\Antrean;
use App\Models\AuditTrail;
use App\Models\Pasien;
use App\Models\Pengguna;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('panel'));
    }

    private function petugas(): Pengguna
    {
        return Pengguna::where('Username', 'petugas')->first();
    }

    private function antrean(string $noAntrean): Antrean
    {
        return Antrean::where('No_Antrean', $noAntrean)->firstOrFail();
    }

    public function test_registrasi_akun_mencatat_audit_pasien_tanpa_pengguna(): void
    {
        $this->registrasiAkun(['Nama_Lengkap' => 'Budi Santoso']);

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Pasien',
            'ID_Pengguna' => null,
        ]);

        $this->assertSame(1, AuditTrail::where('Entitas_Terdampak', 'Pasien')->count());

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Akun',
            'ID_Pengguna' => null,
        ]);
    }

    public function test_input_bpjs_mencatat_audit_dengan_id_pengguna(): void
    {
        $petugas = $this->petugas();

        $this->actingAs($petugas);

        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-01',
                'jenis' => 'BPJS',
                'No_BPJS' => '0001234567890',
                'Nama_Lengkap' => 'Wati Rahayu',
                'Tgl_Lahir' => '1970-01-01',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Pasien',
            'ID_Pengguna' => $petugas->ID_Pengguna,
        ]);
    }

    public function test_perubahan_data_pasien_mencatat_audit(): void
    {
        $pasien = Pasien::create([
            'ID_Pasien' => 'PSN-01',
            'Nama_Lengkap' => 'Lina Marlina',
            'Tgl_Lahir' => '1985-02-20',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ]);

        $pasien->update(['Alamat' => 'Jl. Baru No. 2']);

        $this->assertSame(
            2,
            AuditTrail::where('Entitas_Terdampak', 'Pasien')
                ->where('Deskripsi_Aksi', 'like', '%Lina Marlina%')
                ->count()
        );
    }

    public function test_checkin_mencatat_audit_antrean(): void
    {
        $petugas = $this->petugas();

        $this->daftarkanAntrean($this->buatPasien(['Nama_Lengkap' => 'Budi Santoso']));

        $this->actingAs($petugas);

        Livewire::test(ListAntreans::class)
            ->callTableAction('checkin', $this->antrean('A-001'), [
                'no_rm' => $this->antrean('A-001')->pasien->No_RM,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Antrean',
            'ID_Pengguna' => $petugas->ID_Pengguna,
            'Deskripsi_Aksi' => 'Check-in pasien A-001 (Budi Santoso)',
        ]);

        $this->assertSame(Antrean::STATUS_MENUNGGU, $this->antrean('A-001')->Status);
    }
}
