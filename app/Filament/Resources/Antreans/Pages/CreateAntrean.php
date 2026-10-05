<?php

namespace App\Filament\Resources\Antreans\Pages;

use App\Filament\Resources\Antreans\AntreanResource;
use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use App\Services\QueueService;
use App\Services\RekamMedisService;
use App\Support\NomorTelepon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CreateAntrean extends CreateRecord
{
    protected static string $resource = AntreanResource::class;

    protected static ?string $title = 'Input Pasien (BPJS / Walk-in)';

    protected function handleRecordCreation(array $data): Antrean
    {
        $jadwal = Jadwal::query()->findOrFail($data['ID_Jadwal']);

        $pasien = new Pasien([
            'ID_Pasien' => (string) Str::uuid(),
            'No_RM' => app(RekamMedisService::class)->nomorBaru(),
            'Nama_Lengkap' => $data['Nama_Lengkap'],
            'Tgl_Lahir' => $data['Tgl_Lahir'],
            'Alamat' => $data['Alamat'] ?? null,
            'No_Telepon' => filled($data['No_Telepon'] ?? null)
                ? NomorTelepon::normalisasi($data['No_Telepon'])
                : null,
            'Jenis_Pasien' => $data['jenis'],
            'No_BPJS' => $data['jenis'] === Pasien::JENIS_BPJS ? ($data['No_BPJS'] ?? null) : null,
        ]);

        try {
            return app(QueueService::class)->daftar($jadwal, $pasien);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'data.ID_Jadwal' => $e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('kartu', $this->record->No_Antrean);
    }
}
