<?php

namespace App\Filament\Resources\Dokters\Pages;

use App\Filament\Resources\Dokters\DokterResource;
use App\Models\Dokter;
use App\Support\KodeGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateDokter extends CreateRecord
{
    protected static string $resource = DokterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ID_Dokter'] = KodeGenerator::berikutnya(Dokter::class, 'DOK');

        return $data;
    }
}
