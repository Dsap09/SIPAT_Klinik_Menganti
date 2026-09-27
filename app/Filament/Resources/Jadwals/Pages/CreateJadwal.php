<?php

namespace App\Filament\Resources\Jadwals\Pages;

use App\Filament\Resources\Jadwals\JadwalResource;
use App\Models\Jadwal;
use App\Support\KodeGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwal extends CreateRecord
{
    protected static string $resource = JadwalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ID_Jadwal'] = KodeGenerator::berikutnya(Jadwal::class, 'JDW');

        if (blank($data['Sisa_Kuota'] ?? null)) {
            $data['Sisa_Kuota'] = $data['Kuota_Maksimal'];
        }

        return $data;
    }
}
