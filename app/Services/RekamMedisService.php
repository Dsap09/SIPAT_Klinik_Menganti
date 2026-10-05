<?php

namespace App\Services;

use App\Models\Pasien;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RekamMedisService
{
    public const PANJANG_URUTAN = 4;

    public function nomorBaru(?int $tahun = null): string
    {
        $tahun ??= (int) now()->year;

        return Cache::lock("rm:{$tahun}", 10)->block(10, function () use ($tahun) {
            return DB::transaction(fn () => $this->hitung($tahun));
        });
    }

    public function format(int $tahun, int $urutan): string
    {
        return "RM-{$tahun}-".str_pad((string) $urutan, self::PANJANG_URUTAN, '0', STR_PAD_LEFT);
    }

    private function hitung(int $tahun): string
    {
        $terakhir = Pasien::query()
            ->where('No_RM', 'like', "RM-{$tahun}-%")
            ->orderByDesc('No_RM')
            ->value('No_RM');

        $urutan = $terakhir === null || strlen((string) $terakhir) < self::PANJANG_URUTAN
            ? 1
            : ((int) substr((string) $terakhir, -self::PANJANG_URUTAN)) + 1;

        return $this->format($tahun, $urutan);
    }
}
