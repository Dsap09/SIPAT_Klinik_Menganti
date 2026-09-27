<?php

namespace App\Filament\Resources\Polis\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PoliForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Nama_Poli')
                    ->label('Nama Poli')
                    ->required()
                    ->maxLength(255),
                Textarea::make('Deskripsi')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }
}
