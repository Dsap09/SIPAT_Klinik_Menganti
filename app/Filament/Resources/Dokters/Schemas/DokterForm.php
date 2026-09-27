<?php

namespace App\Filament\Resources\Dokters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DokterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Nama_Dokter')
                    ->label('Nama Dokter')
                    ->required()
                    ->maxLength(255),
                TextInput::make('Spesialisasi')
                    ->label('Spesialisasi')
                    ->maxLength(255),
                Select::make('ID_Poli')
                    ->label('Poli')
                    ->relationship('poli', 'Nama_Poli')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
