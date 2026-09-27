<?php

namespace App\Services;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class QueueService
{
    public function daftar(Jadwal $jadwal, Pasien $pasien, ?string $tanggal = null): Antrean
    {
        $tanggal ??= now()->toDateString();

        return Cache::lock("antrean:{$tanggal}", 10)->block(10, function () use ($jadwal, $pasien, $tanggal) {
            return DB::transaction(function () use ($jadwal, $pasien, $tanggal) {
                $jadwal = Jadwal::query()->lockForUpdate()->findOrFail($jadwal->getKey());

                if (! $jadwal->tersedia()) {
                    throw new RuntimeException('Kuota jadwal ini sudah habis.');
                }

                if (! $pasien->exists) {
                    $pasien->save();
                }

                $antrean = Antrean::create([
                    'ID_Antrean' => (string) Str::uuid(),
                    'No_Antrean' => $this->nomorBerikutnya($tanggal),
                    'Tanggal_Kunjungan' => $tanggal,
                    'Status' => Antrean::STATUS_MENUNGGU,
                    'ID_Pasien' => $pasien->getKey(),
                    'ID_Jadwal' => $jadwal->getKey(),
                ]);

                $jadwal->decrement('Sisa_Kuota');

                return $antrean;
            });
        });
    }

    public function nomorBerikutnya(string $tanggal): string
    {
        $terakhir = Antrean::query()
            ->whereDate('Tanggal_Kunjungan', $tanggal)
            ->orderByDesc('No_Antrean')
            ->value('No_Antrean');

        $urutan = $terakhir === null ? 1 : ((int) Str::afterLast($terakhir, '-')) + 1;

        return 'A-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }
}
