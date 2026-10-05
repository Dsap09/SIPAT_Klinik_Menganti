<?php

namespace Tests\Feature;

use App\Filament\Resources\Antreans\Pages\CreateAntrean;
use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pengguna;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditAntreanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('panel'));
    }

    private function daftarUmum(int $nomor): void
    {
        $pasien = $this->buatPasien(['Nama_Lengkap' => "Pasien Umum {$nomor}"]);

        $this->daftarkanAntrean($pasien);
    }

    private function inputBpjs(int $nomor): void
    {
        Livewire::test(CreateAntrean::class)
            ->fillForm([
                'ID_Jadwal' => 'JDW-02',
                'jenis' => 'BPJS',
                'No_BPJS' => '00012345678'.($nomor % 10),
                'Nama_Lengkap' => "Pasien BPJS {$nomor}",
                'Tgl_Lahir' => '1970-01-01',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_tidak_ada_nomor_antrean_duplikat_atau_tumpang_tindih_dalam_sehari(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->daftarUmum($i);
        }

        $this->actingAs(Pengguna::where('Username', 'petugas')->first());

        for ($i = 6; $i <= 8; $i++) {
            $this->inputBpjs($i);
        }

        $duplikat = Antrean::query()
            ->selectRaw('Tanggal_Kunjungan, No_Antrean, COUNT(*) as jumlah')
            ->groupBy('Tanggal_Kunjungan', 'No_Antrean')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->assertCount(0, $duplikat, 'Ditemukan nomor antrean duplikat.');

        $this->assertSame(
            ['A-001', 'A-002', 'A-003', 'A-004', 'A-005', 'A-006', 'A-007', 'A-008'],
            Antrean::orderBy('No_Antrean')->pluck('No_Antrean')->all()
        );

        $this->assertSame(8, Antrean::query()->distinct()->count('No_Antrean'));
    }

    public function test_kuota_konsisten_setelah_batal_dan_jadwal_ulang(): void
    {
        $this->daftarUmum(1);
        $this->daftarUmum(2);

        $this->assertSame(18, Jadwal::find('JDW-01')->Sisa_Kuota);

        $this->post(route('pendaftaran.batal', 'A-001'))->assertSessionHas('sukses');
        $this->assertSame(19, Jadwal::find('JDW-01')->Sisa_Kuota);

        $this->post(route('pendaftaran.jadwalUlang.simpan', 'A-002'), ['ID_Jadwal' => 'JDW-02'])
            ->assertSessionHas('sukses');

        $this->assertSame(20, Jadwal::find('JDW-01')->Sisa_Kuota);
        $this->assertSame(14, Jadwal::find('JDW-02')->Sisa_Kuota);
        $this->assertSame(1, Antrean::where('Status', Antrean::STATUS_MENUNGGU)->count());
    }

    public function test_registrasi_akun_dibatasi_setelah_batas_per_menit(): void
    {
        config()->set('sipat.throttle.pendaftaran', 2);

        $this->registrasiAkun(['Nama_Lengkap' => 'Pasien Umum 1', 'No_Telepon' => '081200000001']);
        $this->registrasiAkun(['Nama_Lengkap' => 'Pasien Umum 2', 'No_Telepon' => '081200000002']);

        $this->post(route('pasien.register.simpan'), [
            'Nama_Lengkap' => 'Pasien Umum 3',
            'Tempat_Lahir' => 'Gresik',
            'Tgl_Lahir' => '1990-01-01',
            'Jenis_Kelamin' => 'Laki-laki',
            'NIK' => '3525123456780003',
            'Alamat' => 'Jl. Raya Menganti',
            'No_Telepon' => '081200000003',
            'Jenis_Pasien' => 'UMUM',
        ])->assertStatus(429);
    }
}
