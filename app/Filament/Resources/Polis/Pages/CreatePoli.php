<?php

namespace App\Filament\Resources\Polis\Pages;

use App\Filament\Resources\Polis\PoliResource;
use App\Models\Poli;
use App\Support\KodeGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreatePoli extends CreateRecord
{
    protected static string $resource = PoliResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ID_Poli'] = KodeGenerator::berikutnya(Poli::class, 'POLI');

        return $data;
    }
}
