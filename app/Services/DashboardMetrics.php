<?php

namespace App\Services;

use App\Models\Antrean;
use App\Models\Pasien;

class DashboardMetrics
{
    /**
     * @return array<string, int>
     */
    public function hari(?string $tanggal = null): array
    {
        $tanggal ??= now()->toDateString();

        $query = Antrean::query()->padaTanggal($tanggal);

        return [
            'total' => (clone $query)->count(),
            'bpjs' => (clone $query)->whereHas('pasien', fn ($q) => $q->where('Jenis_Pasien', Pasien::JENIS_BPJS))->count(),
            'umum' => (clone $query)->whereHas('pasien', fn ($q) => $q->where('Jenis_Pasien', Pasien::JENIS_UMUM))->count(),
            'menunggu' => (clone $query)->where('Status', Antrean::STATUS_MENUNGGU)->count(),
            'dilayani' => (clone $query)->where('Status', Antrean::STATUS_DILAYANI)->count(),
            'selesai' => (clone $query)->where('Status', Antrean::STATUS_SELESAI)->count(),
            'batal' => (clone $query)->where('Status', Antrean::STATUS_BATAL)->count(),
            'checkin' => (clone $query)->whereNotNull('Waktu_CheckIn')->count(),
        ];
    }
}
