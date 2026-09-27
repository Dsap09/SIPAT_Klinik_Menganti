<?php

namespace App\Services;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class QueueService
{
    public const MENIT_PER_PASIEN = 15;

    public function daftar(Jadwal $jadwal, Pasien $pasien, ?string $tanggal = null): Antrean
    {
        $tanggal ??= now()->toDateString();

        return Cache::lock("antrean:{$tanggal}", 10)->block(10, function () use ($jadwal, $pasien, $tanggal) {
            return DB::transaction(fn () => $this->buat($jadwal, $pasien, $tanggal));
        });
    }

    public function batalkan(Antrean $antrean): void
    {
        DB::transaction(function () use ($antrean) {
            $antrean = Antrean::query()->lockForUpdate()->findOrFail($antrean->getKey());

            if (! $antrean->bisaDibatalkan()) {
                throw new RuntimeException('Antrean ini tidak dapat dibatalkan.');
            }

            $this->lepasKuota($antrean);

            $antrean->Status = Antrean::STATUS_BATAL;
            $antrean->save();
        });
    }

    public function jadwalkanUlang(Antrean $lama, Jadwal $baru): Antrean
    {
        $tanggal = now()->toDateString();

        return Cache::lock("antrean:{$tanggal}", 10)->block(10, function () use ($lama, $baru, $tanggal) {
            return DB::transaction(function () use ($lama, $baru, $tanggal) {
                $lama = Antrean::query()->lockForUpdate()->findOrFail($lama->getKey());

                if (! $lama->bisaDibatalkan()) {
                    throw new RuntimeException('Antrean ini tidak dapat dijadwalkan ulang.');
                }

                $this->lepasKuota($lama);

                $lama->Status = Antrean::STATUS_BATAL;
                $lama->save();

                return $this->buat($baru, $lama->pasien, $tanggal);
            });
        });
    }

    public function nomorBerikutnya(string $tanggal): string
    {
        $terakhir = Antrean::query()
            ->padaTanggal($tanggal)
            ->orderByDesc('No_Antrean')
            ->value('No_Antrean');

        $urutan = $terakhir === null ? 1 : ((int) Str::afterLast($terakhir, '-')) + 1;

        return 'A-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }

    private function buat(Jadwal $jadwal, Pasien $pasien, string $tanggal): Antrean
    {
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
            'Estimasi_Waktu' => $this->estimasi($jadwal, $tanggal),
            'Status' => Antrean::STATUS_MENUNGGU,
            'ID_Pasien' => $pasien->getKey(),
            'ID_Jadwal' => $jadwal->getKey(),
        ]);

        $jadwal->decrement('Sisa_Kuota');

        return $antrean;
    }

    private function lepasKuota(Antrean $antrean): void
    {
        $jadwal = Jadwal::query()->lockForUpdate()->findOrFail($antrean->ID_Jadwal);

        $jadwal->Sisa_Kuota = min($jadwal->Sisa_Kuota + 1, $jadwal->Kuota_Maksimal);
        $jadwal->save();
    }

    private function estimasi(Jadwal $jadwal, string $tanggal): string
    {
        $posisi = Antrean::query()
            ->padaTanggal($tanggal)
            ->where('Status', '!=', Antrean::STATUS_BATAL)
            ->count() + 1;

        $mulai = Carbon::parse($jadwal->Jam_Mulai);
        $selesai = Carbon::parse($jadwal->Jam_Selesai);

        $estimasi = $mulai->copy()->addMinutes(($posisi - 1) * self::MENIT_PER_PASIEN);

        if ($estimasi->gt($selesai)) {
            $estimasi = $selesai;
        }

        return $estimasi->format('H:i:s');
    }
}
