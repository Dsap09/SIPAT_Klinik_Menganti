<?php

namespace Tests;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use App\Services\QueueService;
use App\Services\RekamMedisService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * Membuat data pasien langsung (tanpa alur HTTP) untuk kebutuhan pengujian.
     */
    protected function buatPasien(array $atribut = []): Pasien
    {
        return Pasien::create(array_merge([
            'ID_Pasien' => (string) Str::uuid(),
            'No_RM' => app(RekamMedisService::class)->nomorBaru(),
            'Nama_Lengkap' => 'Pasien Uji',
            'Tgl_Lahir' => '1990-05-12',
            'Jenis_Pasien' => Pasien::JENIS_UMUM,
        ], $atribut));
    }

    /**
     * Mendaftarkan pasien ke antrean pada jadwal tertentu melalui QueueService.
     */
    protected function daftarkanAntrean(Pasien $pasien, string $idJadwal = 'JDW-01'): Antrean
    {
        return app(QueueService::class)->daftar(Jadwal::findOrFail($idJadwal), $pasien);
    }

    /**
     * Membuat akun pasien melalui endpoint registrasi (termasuk login otomatis).
     */
    protected function registrasiAkun(array $atribut = []): Pasien
    {
        $this->post(route('pasien.register.simpan'), array_merge([
            'Nama_Lengkap' => 'Budi Santoso',
            'Tempat_Lahir' => 'Gresik',
            'Tgl_Lahir' => '1990-05-12',
            'Jenis_Kelamin' => 'Laki-laki',
            'NIK' => (string) random_int(1000000000000000, 9999999999999999),
            'Alamat' => 'Jl. Raya Menganti No. 1',
            'No_Telepon' => '0812'.random_int(1000000, 9999999),
            'Jenis_Pasien' => 'UMUM',
        ], $atribut))->assertSessionHasNoErrors();

        return Pasien::query()->orderByDesc('No_RM')->firstOrFail();
    }
}
