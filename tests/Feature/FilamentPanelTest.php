<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\Pengguna;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FilamentPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('panel'));
    }

    private function pengguna(string $username): Pengguna
    {
        return Pengguna::where('Username', $username)->first();
    }

    public function test_tamu_diarahkan_ke_login_panel(): void
    {
        $this->get('/panel')->assertRedirect('/panel/login');
    }

    public function test_petugas_bisa_masuk_lewat_panel(): void
    {
        $petugas = $this->pengguna('petugas');

        Livewire::test(Login::class)
            ->fillForm(['Username' => 'petugas', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($petugas);
    }

    public function test_password_salah_ditolak_di_panel(): void
    {
        Livewire::test(Login::class)
            ->fillForm(['Username' => 'petugas', 'password' => 'salah'])
            ->call('authenticate')
            ->assertHasFormErrors(['Username']);

        $this->assertGuest();
    }

    public function test_semua_peran_staf_bisa_mengakses_panel(): void
    {
        foreach (['admin', 'petugas', 'manajemen', 'dokter'] as $username) {
            $pengguna = $this->pengguna($username);

            $this->assertTrue(
                $pengguna->canAccessPanel(Filament::getPanel('panel')),
                "Peran {$pengguna->Role} seharusnya bisa mengakses panel."
            );
        }
    }

    public function test_admin_bisa_membuka_master_poli(): void
    {
        $this->actingAs($this->pengguna('admin'))
            ->get('/panel/polis')
            ->assertOk();
    }

    public function test_petugas_ditolak_di_master_poli(): void
    {
        $this->actingAs($this->pengguna('petugas'))
            ->get('/panel/polis')
            ->assertForbidden();
    }

    public function test_petugas_bisa_membuka_antrean_loket(): void
    {
        $this->actingAs($this->pengguna('petugas'))
            ->get('/panel/antreans')
            ->assertOk();
    }

    public function test_dokter_ditolak_di_antrean_loket(): void
    {
        $this->actingAs($this->pengguna('dokter'))
            ->get('/panel/antreans')
            ->assertForbidden();
    }

    public function test_pengguna_bisa_keluar_dari_panel(): void
    {
        $this->actingAs($this->pengguna('petugas'))
            ->post('/panel/logout')
            ->assertRedirect();

        $this->assertGuest();
    }

    #[DataProvider('peranProvider')]
    public function test_panduan_pengguna_bisa_dibuka_semua_peran(string $username): void
    {
        $this->actingAs($this->pengguna($username))
            ->get('/panel/panduan')
            ->assertOk()
            ->assertSee('Panduan Singkat Pengguna SIPAT');
    }

    public function test_login_dengan_remember_tidak_menyentuh_kolom_remember_token(): void
    {
        $petugas = $this->pengguna('petugas');

        Auth::login($petugas, true);

        $this->assertAuthenticatedAs($petugas);
    }

    public static function peranProvider(): array
    {
        return [['admin'], ['petugas'], ['manajemen'], ['dokter']];
    }
}
