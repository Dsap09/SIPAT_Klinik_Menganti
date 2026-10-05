<?php

namespace Tests\Unit;

use App\Models\Pasien;
use App\Services\RekamMedisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class RekamMedisServiceTest extends TestCase
{
    use RefreshDatabase;

    private function layanan(): RekamMedisService
    {
        return app(RekamMedisService::class);
    }

    private function pasienDenganRm(string $noRm): Pasien
    {
        return Pasien::create([
            'ID_Pasien' => (string) Str::uuid(),
            'No_RM' => $noRm,
            'Nama_Lengkap' => 'Pasien '.$noRm,
            'Tgl_Lahir' => '1990-01-01',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ]);
    }

    public function test_format_nomor_rm(): void
    {
        $this->assertSame('RM-2026-0001', $this->layanan()->format(2026, 1));
        $this->assertSame('RM-2026-0042', $this->layanan()->format(2026, 42));
        $this->assertSame('RM-2026-9999', $this->layanan()->format(2026, 9999));
    }

    public function test_nomor_pertama_pada_tahun_berjalan(): void
    {
        Carbon::setTestNow('2026-03-01 08:00:00');

        $this->assertSame('RM-2026-0001', $this->layanan()->nomorBaru());
    }

    public function test_nomor_berurutan_setelah_data_terakhir(): void
    {
        Carbon::setTestNow('2026-03-01 08:00:00');

        $this->pasienDenganRm('RM-2026-0001');
        $this->pasienDenganRm('RM-2026-0002');

        $this->assertSame('RM-2026-0003', $this->layanan()->nomorBaru());
    }

    public function test_urutan_di_reset_pada_tahun_baru(): void
    {
        Carbon::setTestNow('2026-12-31 23:00:00');

        $this->pasienDenganRm('RM-2026-0007');

        Carbon::setTestNow('2027-01-01 00:05:00');

        $this->assertSame('RM-2027-0001', $this->layanan()->nomorBaru());
    }

    public function test_urutan_mengabaikan_data_dari_tahun_lain(): void
    {
        Carbon::setTestNow('2027-05-01 08:00:00');

        $this->pasienDenganRm('RM-2026-0099');

        $this->assertSame('RM-2027-0001', $this->layanan()->nomorBaru());
    }
}
