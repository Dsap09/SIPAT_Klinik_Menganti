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

    public function test_halaman_form_pendaftaran_tampil(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Pendaftaran Online Pasien Umum');
    }

    public function test_pasien_baru_bisa_mendaftar_dan_mendapat_nomor_antrean(): void
    {
        $response = $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Budi Santoso',
            'Tgl_Lahir' => '1990-05-12',
            'Alamat' => 'Jl. Raya Menganti No. 1',
        ]);

        $response->assertRedirect(route('pendaftaran.status', 'A-001'));

        $this->assertDatabaseHas('PASIEN', [
            'Nama_Lengkap' => 'Budi Santoso',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
            'No_RM' => null,
        ]);

        $this->assertDatabaseHas('ANTREAN', [
            'No_Antrean' => 'A-001',
            'Status' => Antrean::STATUS_MENUNGGU,
        ]);

        $this->assertNotNull(Antrean::where('No_Antrean', 'A-001')->first()->Estimasi_Waktu);

        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_nomor_antrean_berurutan_tanpa_duplikat(): void
    {
        foreach (['Budi', 'Siti', 'Agus'] as $nama) {
            $this->post('/daftar', [
                'ID_Jadwal' => 'JDW-01',
                'jenis' => 'baru',
                'Nama_Lengkap' => $nama,
                'Tgl_Lahir' => '1990-05-12',
                'Alamat' => 'Jl. Raya Menganti',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(
            ['A-001', 'A-002', 'A-003'],
            Antrean::orderBy('No_Antrean')->pluck('No_Antrean')->all()
        );

        $this->assertSame(3, Antrean::distinct()->count('No_Antrean'));
    }

    public function test_pasien_lama_bisa_mendaftar_dengan_no_rm(): void
    {
        $pasien = Pasien::create([
            'ID_Pasien' => 'PSN-01',
            'No_RM' => 'RM-000123',
            'Nama_Lengkap' => 'Lina Marlina',
            'Tgl_Lahir' => '1985-02-20',
            'Alamat' => 'Menganti',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ]);

        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'lama',
            'No_RM' => 'RM-000123',
        ])->assertRedirect(route('pendaftaran.status', 'A-001'));

        $this->assertDatabaseHas('ANTREAN', [
            'ID_Pasien' => $pasien->ID_Pasien,
            'No_Antrean' => 'A-001',
        ]);

        $this->assertSame(1, Pasien::count());
    }

    public function test_no_rm_tidak_valid_ditolak(): void
    {
        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'lama',
            'No_RM' => 'RM-TIDAK-ADA',
        ])->assertSessionHasErrors('No_RM');

        $this->assertDatabaseCount('ANTREAN', 0);
    }

    public function test_kuota_habis_ditolak(): void
    {
        Jadwal::where('ID_Jadwal', 'JDW-01')->update(['Sisa_Kuota' => 1]);

        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Pasien Pertama',
            'Tgl_Lahir' => '1992-03-03',
        ])->assertSessionHasNoErrors();

        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Pasien Kedua',
            'Tgl_Lahir' => '1993-04-04',
        ])->assertSessionHasErrors('ID_Jadwal');

        $this->assertDatabaseCount('ANTREAN', 1);
        $this->assertSame(0, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_halaman_status_menampilkan_nomor_antrean(): void
    {
        $this->post('/daftar', [
            'ID_Jadwal' => 'JDW-01',
            'jenis' => 'baru',
            'Nama_Lengkap' => 'Budi Santoso',
            'Tgl_Lahir' => '1990-05-12',
        ])->assertSessionHasNoErrors();

        $this->get('/status/A-001')
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('Budi Santoso');
    }
}
