<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardStats;
use App\Models\Antrean;
use App\Models\Pengguna;
use App\Services\DashboardMetrics;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    private function daftarUmum(string $nama = 'Budi Santoso', string $jadwal = 'JDW-01'): void
    {
        $pasien = $this->buatPasien(['Nama_Lengkap' => $nama]);

        $this->daftarkanAntrean($pasien, $jadwal);
    }

    public function test_metrik_dashboard_sama_persis_dengan_basis_data(): void
    {
        $this->daftarUmum('Budi Santoso');

        $this->daftarUmum('Siti Aminah', 'JDW-02');

        Antrean::where('No_Antrean', 'A-001')->update([
            'Status' => Antrean::STATUS_SELESAI,
            'Waktu_CheckIn' => now(),
        ]);

        $metrik = app(DashboardMetrics::class)->hari();

        $basis = Antrean::whereDate('Tanggal_Kunjungan', today());

        $this->assertSame((clone $basis)->count(), $metrik['total']);
        $this->assertSame(2, $metrik['umum']);
        $this->assertSame(0, $metrik['bpjs']);
        $this->assertSame((clone $basis)->where('Status', Antrean::STATUS_SELESAI)->count(), $metrik['selesai']);
        $this->assertSame((clone $basis)->whereNotNull('Waktu_CheckIn')->count(), $metrik['checkin']);
        $this->assertSame(
            $metrik['total'],
            $metrik['menunggu'] + $metrik['dilayani'] + $metrik['selesai'] + $metrik['batal']
        );
    }

    public function test_manajemen_bisa_membuka_dashboard_dengan_angka(): void
    {
        $this->daftarUmum('Budi Santoso');

        $this->actingAs($this->pengguna('manajemen'));

        $this->get('/panel')->assertOk();

        Livewire::test(DashboardStats::class)
            ->assertSee('Total Pasien Hari Ini')
            ->assertSee('Pasien BPJS')
            ->assertSee('Pasien Umum');
    }

    public function test_widget_dashboard_tidak_tampil_untuk_petugas(): void
    {
        $this->assertFalse(DashboardStats::canView());

        $this->actingAs($this->pengguna('petugas'));

        $this->assertFalse(DashboardStats::canView());
    }
}
