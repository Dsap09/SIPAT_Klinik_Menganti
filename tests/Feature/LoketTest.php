<?php

namespace Tests\Feature;

use App\Filament\Resources\Antreans\Pages\CreateAntrean;
use App\Filament\Resources\Antreans\Pages\ListAntreans;
use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pengguna;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoketTest extends TestCase
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

    private function daftarUmum(string $nama = 'Budi Santoso', string $tglLahir = '1990-05-12'): void
    {
        $pasien = $this->buatPasien([
            'Nama_Lengkap' => $nama,
            'Tgl_Lahir' => $tglLahir,
        ]);

        $this->daftarkanAntrean($pasien);
    }

    private function antrean(string $noAntrean): Antrean
    {
        return Antrean::where('No_Antrean', $noAntrean)->firstOrFail();
    }

    public function test_petugas_bisa_input_pasien_bpjs_lewat_panel(): void
    {
        $this->actingAs($this->petugas());

        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-01',
                'jenis' => 'BPJS',
                'No_BPJS' => '0001234567890',
                'Nama_Lengkap' => 'Wati Rahayu',
                'Tgl_Lahir' => '1970-01-01',
                'Alamat' => 'Menganti',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('PASIEN', [
            'Nama_Lengkap' => 'Wati Rahayu',
            'Jenis_Pasien' => 'BPJS',
            'No_BPJS' => '0001234567890',
        ]);

        $this->assertDatabaseHas('ANTREAN', ['No_Antrean' => 'A-001']);
        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_no_bpjs_wajib_diisi(): void
    {
        $this->actingAs($this->petugas());

        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-01',
                'jenis' => 'BPJS',
                'Nama_Lengkap' => 'Wati Rahayu',
                'Tgl_Lahir' => '1970-01-01',
            ])
            ->call('create')
            ->assertHasFormErrors(['No_BPJS']);

        $this->assertDatabaseCount('ANTREAN', 0);
    }

    public function test_pasien_umum_walk_in_bisa_diinput_lewat_panel(): void
    {
        $this->actingAs($this->petugas());

        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-01',
                'jenis' => 'UMUM',
                'Nama_Lengkap' => 'Slamet Umum',
                'Tgl_Lahir' => '1980-03-03',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('PASIEN', [
            'Nama_Lengkap' => 'Slamet Umum',
            'Jenis_Pasien' => 'UMUM',
            'No_BPJS' => null,
        ]);
    }

    public function test_nomor_antrean_bpjs_menyambung_antrean_umum_online(): void
    {
        $this->daftarUmum('Budi Santoso');

        $this->actingAs($this->petugas());

        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-02',
                'jenis' => 'BPJS',
                'No_BPJS' => '0001234567890',
                'Nama_Lengkap' => 'Wati Rahayu',
                'Tgl_Lahir' => '1970-01-01',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['A-001', 'A-002'],
            Antrean::orderBy('No_Antrean')->pluck('No_Antrean')->all()
        );
    }

    public function test_checkin_gagal_jika_no_rm_tidak_cocok(): void
    {
        $this->daftarUmum('Budi Santoso', '1990-05-12');

        $this->actingAs($this->petugas());

        Livewire::test(ListAntreans::class)
            ->callTableAction('checkin', $this->antrean('A-001'), [
                'no_rm' => 'RM-2099-9999',
            ]);

        $this->assertNull($this->antrean('A-001')->Waktu_CheckIn);
        $this->assertSame(Antrean::STATUS_MENUNGGU, $this->antrean('A-001')->Status);
        $this->assertDatabaseMissing('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Antrean',
        ]);
    }

    public function test_checkin_berhasil_lalu_kartu_antrian_tampil(): void
    {
        $this->daftarUmum('Budi Santoso', '1990-05-12');

        $this->actingAs($this->petugas());

        $noRm = $this->antrean('A-001')->pasien->No_RM;

        Livewire::test(ListAntreans::class)
            ->assertSee($noRm)
            ->callTableAction('checkin', $this->antrean('A-001'), [
                'no_rm' => strtolower($noRm),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertNotNull($this->antrean('A-001')->Waktu_CheckIn);
        $this->assertSame(Antrean::STATUS_MENUNGGU, $this->antrean('A-001')->Status);

        $this->get(route('kartu', 'A-001'))
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('Budi Santoso');
    }

    public function test_ubah_status_antrean_dari_panel(): void
    {
        $this->daftarUmum('Budi Santoso');

        $this->actingAs($this->petugas());

        Livewire::test(ListAntreans::class)
            ->callTableAction('ubahStatus', $this->antrean('A-001'), [
                'Status' => Antrean::STATUS_DILAYANI,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(Antrean::STATUS_DILAYANI, $this->antrean('A-001')->Status);
    }

    public function test_kartu_hanya_bisa_diakses_petugas(): void
    {
        $this->daftarUmum('Budi Santoso');

        $this->get(route('kartu', 'A-001'))->assertRedirect('/panel/login');

        $this->actingAs(Pengguna::where('Username', 'dokter')->first())
            ->get(route('kartu', 'A-001'))
            ->assertForbidden();
    }
}
