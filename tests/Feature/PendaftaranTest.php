<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_daftar_menampilkan_gerbang_akun(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Masuk Akun Pasien')
            ->assertSee('Daftar Akun Baru');
    }

    public function test_pasien_terautentikasi_diarahkan_ke_pemilihan_jadwal(): void
    {
        $pasien = $this->buatPasien();

        $this->actingAs($pasien, 'pasien')
            ->get('/daftar')
            ->assertRedirect(route('pasien.daftar'));
    }

    public function test_kuota_habis_ditolak_saat_daftar_berobat(): void
    {
        $pasien = $this->buatPasien();

        $this->actingAs($pasien, 'pasien');

        Jadwal::where('ID_Jadwal', 'JDW-01')->update(['Sisa_Kuota' => 1]);

        $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->buatPasien(['Nama_Lengkap' => 'Pasien Kedua']), 'pasien');

        $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
            ->assertSessionHasErrors('ID_Jadwal');

        $this->assertDatabaseCount('ANTREAN', 1);
        $this->assertSame(0, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_nomor_antrean_berurutan_tanpa_duplikat(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($this->buatPasien(['Nama_Lengkap' => "Pasien {$i}"]), 'pasien');
            $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(
            ['A-001', 'A-002', 'A-003'],
            Antrean::orderBy('No_Antrean')->pluck('No_Antrean')->all()
        );

        $this->assertSame(3, Antrean::distinct()->count('No_Antrean'));
    }

    public function test_halaman_status_menampilkan_nomor_antrean(): void
    {
        $pasien = $this->buatPasien(['Nama_Lengkap' => 'Budi Santoso']);

        $this->daftarkanAntrean($pasien);

        $this->get('/status/A-001')
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('Budi Santoso');
    }

    public function test_pasien_lama_dengan_nomor_rm_bisa_masuk_lewat_login(): void
    {
        $pasien = Pasien::create([
            'ID_Pasien' => 'PSN-01',
            'No_RM' => 'RM-2026-0001',
            'Nama_Lengkap' => 'Lina Marlina',
            'Tgl_Lahir' => '1985-02-20',
            'Alamat' => 'Menganti',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ]);

        $this->post(route('pasien.login.proses'), [
            'No_RM' => 'RM-2026-0001',
            'Tgl_Lahir' => '1985-02-20',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->assertAuthenticatedAs($pasien, 'pasien');

        $this->post(route('pasien.daftar.simpan'), ['ID_Jadwal' => 'JDW-01'])
            ->assertRedirect(route('pendaftaran.status', 'A-001'));

        $this->assertDatabaseHas('ANTREAN', [
            'ID_Pasien' => $pasien->ID_Pasien,
            'No_Antrean' => 'A-001',
        ]);

        $this->assertSame(1, Pasien::count());
    }
}
