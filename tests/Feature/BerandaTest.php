<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_beranda_tampil_dengan_data_poli_dari_basis_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Poli Umum')
            ->assertSee('Poli Gigi')
            ->assertSee('Poli Anak');
    }

    public function test_beranda_menampilkan_jadwal_yang_masih_punya_kuota(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Senin')
            ->assertSee('dr. Andi Pratama')
            ->assertSee('kuota tersisa');
    }

    public function test_beranda_tidak_menampilkan_jadwal_dengan_kuota_habis(): void
    {
        Jadwal::query()->update(['Sisa_Kuota' => 0]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('kuota tersisa')
            ->assertSee('Belum ada jadwal dengan kuota tersedia');
    }

    public function test_cek_status_mengalihkan_ke_halaman_status(): void
    {
        $this->get(route('pendaftaran.cek', ['no' => 'a-001']))
            ->assertRedirect(route('pendaftaran.status', 'A-001'));
    }

    public function test_cek_status_menolak_format_yang_tidak_valid(): void
    {
        $this->get(route('pendaftaran.cek', ['no' => 'bukan-nomor']))
            ->assertRedirect(route('beranda'))
            ->assertSessionHas('error');
    }
}
