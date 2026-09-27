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

    public function test_pendaftaran_online_mencatat_audit_pasien_tanpa_pengguna(): void
    {
        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Budi Santoso',
            'Tgl_Lahir' => '1990-05-12',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Pasien',
            'ID_Pengguna' => null,
        ]);

        $this->assertSame(1, AuditTrail::where('Entitas_Terdampak', 'Pasien')->count());
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

        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Budi Santoso',
            'Tgl_Lahir' => '1990-05-12',
        ])->assertSessionHasNoErrors();

        $this->actingAs($petugas);

        Livewire::test(ListAntreans::class)
            ->callTableAction('checkin', $this->antrean('A-001'), [
                'tanggal_lahir' => '1990-05-12',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Antrean',
            'ID_Pengguna' => $petugas->ID_Pengguna,
            'Deskripsi_Aksi' => 'Check-in pasien A-001 (Budi Santoso)',
        ]);
    }
}
