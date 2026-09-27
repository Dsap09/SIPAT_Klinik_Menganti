<?php

namespace App\Filament\Resources\Polis\Pages;

use App\Filament\Resources\Polis\PoliResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPoli extends EditRecord
{
    protected static string $resource = PoliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
