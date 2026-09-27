<?php

namespace App\Filament\Widgets;

use App\Models\Pengguna;
use App\Services\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Antrean Hari Ini';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->Role, [Pengguna::ROLE_ADMIN, Pengguna::ROLE_MANAJEMEN], true);
    }

    protected function getStats(): array
    {
        $metrik = app(DashboardMetrics::class)->hari();

        return [
            Stat::make('Total Pasien Hari Ini', (string) $metrik['total'])
                ->description('Semua antrean pada hari berjalan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Pasien BPJS', (string) $metrik['bpjs'])
                ->color('info'),
            Stat::make('Pasien Umum', (string) $metrik['umum'])
                ->color('gray'),
            Stat::make('Menunggu', (string) $metrik['menunggu'])
                ->color('warning'),
            Stat::make('Dilayani', (string) $metrik['dilayani'])
                ->color('info'),
            Stat::make('Selesai', (string) $metrik['selesai'])
                ->color('success'),
            Stat::make('Batal', (string) $metrik['batal'])
                ->color('danger'),
            Stat::make('Sudah Check-in', (string) $metrik['checkin'])
                ->color('gray'),
        ];
    }
}
