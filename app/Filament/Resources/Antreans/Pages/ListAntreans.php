<?php

namespace App\Filament\Resources\Antreans\Pages;

use App\Filament\Resources\Antreans\AntreanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAntreans extends ListRecords
{
    protected static string $resource = AntreanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
