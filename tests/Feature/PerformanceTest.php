<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_publik_memuat_di_bawah_tiga_detik_dengan_query_terbatas(): void
    {
        foreach (['/', '/daftar', '/pasien/login', '/pasien/register'] as $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $mulai = microtime(true);
            $this->get($url)->assertOk();
            $durasi = microtime(true) - $mulai;

            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            $this->assertLessThan(3.0, $durasi, "Halaman {$url} memerlukan {$durasi} detik.");
            $this->assertLessThan(15, $jumlah, "Halaman {$url} menjalankan {$jumlah} query (indikasi N+1).");
        }
    }

    public function test_halaman_status_tidak_menjalankan_query_berlebihan(): void
    {
        $this->daftarkanAntrean($this->buatPasien(['Nama_Lengkap' => 'Budi Santoso']));

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('pendaftaran.status', 'A-001'))->assertOk();

        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(15, $jumlah, "Halaman status menjalankan {$jumlah} query (indikasi N+1).");
    }
}
