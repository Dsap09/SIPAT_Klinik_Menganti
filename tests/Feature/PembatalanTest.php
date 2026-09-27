<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Jadwal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembatalanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function daftarUmum(string $jadwal = 'JDW-01', string $nama = 'Budi Santoso'): void
    {
        $this->post('/daftar', [
            'ID_Jadwal' => $jadwal,
            'jenis' => 'baru',
            'Nama_Lengkap' => $nama,
            'Tgl_Lahir' => '1990-05-12',
        ])->assertSessionHasNoErrors();
    }

    private function antrean(string $noAntrean): Antrean
    {
        return Antrean::where('No_Antrean', $noAntrean)->firstOrFail();
    }

    public function test_pasien_bisa_membatalkan_dan_kuota_dikembalikan(): void
    {
        $this->daftarUmum();

        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);

        $this->post(route('pendaftaran.batal', 'A-001'))
            ->assertRedirect(route('pendaftaran.status', 'A-001'))
            ->assertSessionHas('sukses');

        $this->assertSame(Antrean::STATUS_BATAL, $this->antrean('A-001')->Status);
        $this->assertSame(20, Jadwal::find('JDW-01')->Sisa_Kuota);
        $this->assertDatabaseHas('AUDIT_TRAIL', [
            'Entitas_Terdampak' => 'Antrean',
            'Deskripsi_Aksi' => 'Batalkan antrean A-001',
        ]);
    }

    public function test_antrean_yang_sudah_checkin_tidak_bisa_dibatalkan(): void
    {
        $this->daftarUmum();

        $this->antrean('A-001')->update(['Waktu_CheckIn' => now()]);

        $this->post(route('pendaftaran.batal', 'A-001'))->assertSessionHas('error');

        $this->assertSame(Antrean::STATUS_MENUNGGU, $this->antrean('A-001')->Status);
        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_membatalkan_dua_kali_tidak_menggandakan_kuota(): void
    {
        $this->daftarUmum();

        $this->post(route('pendaftaran.batal', 'A-001'))->assertSessionHas('sukses');
        $this->post(route('pendaftaran.batal', 'A-001'))->assertSessionHas('error');

        $this->assertSame(20, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_pasien_bisa_menjadwalkan_ulang_ke_jadwal_lain(): void
    {
        $this->daftarUmum('JDW-01');

        $this->post(route('pendaftaran.jadwalUlang.simpan', 'A-001'), ['ID_Jadwal' => 'JDW-02'])
            ->assertRedirect(route('pendaftaran.status', 'A-002'))
            ->assertSessionHas('sukses');

        $this->assertSame(Antrean::STATUS_BATAL, $this->antrean('A-001')->Status);
        $this->assertSame(20, Jadwal::find('JDW-01')->Sisa_Kuota);
        $this->assertSame(14, Jadwal::find('JDW-02')->Sisa_Kuota);

        $this->assertDatabaseHas('ANTREAN', [
            'No_Antrean' => 'A-002',
            'Status' => Antrean::STATUS_MENUNGGU,
            'ID_Jadwal' => 'JDW-02',
        ]);
    }

    public function test_form_jadwal_ulang_ditolak_setelah_checkin(): void
    {
        $this->daftarUmum();

        $this->antrean('A-001')->update(['Waktu_CheckIn' => now()]);

        $this->get(route('pendaftaran.jadwalUlang.form', 'A-001'))->assertForbidden();
    }

    public function test_jadwal_ulang_gagal_jika_kuota_tujuan_habis(): void
    {
        $this->daftarUmum('JDW-01');

        Jadwal::where('ID_Jadwal', 'JDW-02')->update(['Sisa_Kuota' => 0]);

        $this->post(route('pendaftaran.jadwalUlang.simpan', 'A-001'), ['ID_Jadwal' => 'JDW-02'])
            ->assertSessionHasErrors('ID_Jadwal');

        $this->assertSame(Antrean::STATUS_MENUNGGU, $this->antrean('A-001')->Status);
        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);
    }

    public function test_endpoint_notifikasi_menampilkan_status_terbaru(): void
    {
        $this->daftarUmum();

        $this->getJson(route('pendaftaran.status.data', 'A-001'))
            ->assertOk()
            ->assertJson([
                'no_antrean' => 'A-001',
                'status' => Antrean::STATUS_MENUNGGU,
                'pesan' => 'Antrean A-001 berstatus Menunggu.',
            ])
            ->assertJsonStructure(['status', 'estimasi_waktu', 'checkin', 'diperbarui_pada']);

        $this->antrean('A-001')->update(['Status' => Antrean::STATUS_DILAYANI]);

        $this->getJson(route('pendaftaran.status.data', 'A-001'))
            ->assertOk()
            ->assertJson([
                'status' => Antrean::STATUS_DILAYANI,
                'pesan' => 'Antrean A-001 berstatus Dilayani.',
            ]);
    }
}
