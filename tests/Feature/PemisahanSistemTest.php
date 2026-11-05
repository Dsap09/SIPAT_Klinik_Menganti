<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemisahanSistemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_sesi_pasien_dan_staf_dapat_aktif_bersamaan_tanpa_saling_menggantikan(): void
    {
        $pasien = $this->buatPasien();
        $petugas = Pengguna::where('Username', 'petugas')->firstOrFail();

        $this->actingAs($petugas, 'web');
        $this->actingAs($pasien, 'pasien');

        $this->assertTrue(auth('pasien')->check());
        $this->assertTrue(auth('web')->check());
        $this->assertSame($pasien->getKey(), auth('pasien')->id());
        $this->assertSame($petugas->getKey(), auth('web')->id());

        $this->get('/')->assertOk();
    }

    public function test_sesi_pasien_tidak_membuka_panel_staf(): void
    {
        $pasien = $this->buatPasien();

        $this->actingAs($pasien, 'pasien');

        $this->get('/panel')->assertRedirect();
        $this->assertGuest('web');
    }

    public function test_mode_pasien_mematikan_panel_staf_dan_kartu(): void
    {
        config()->set('sipat.mode', 'pasien');

        $this->get('/')->assertOk();
        $this->get('/pasien/login')->assertOk();
        $this->get('/panel')->assertNotFound();
        $this->get('/panel/admin')->assertNotFound();
        $this->get('/kartu/A-001')->assertNotFound();
    }

    public function test_mode_staf_mematikan_seluruh_halaman_publik_pasien(): void
    {
        config()->set('sipat.mode', 'staf');

        $this->get('/')->assertNotFound();
        $this->get('/pasien/login')->assertNotFound();
        $this->get('/pasien/register')->assertNotFound();
        $this->get('/status/A-001')->assertNotFound();

        $this->get('/panel')->assertRedirect();
    }
}
