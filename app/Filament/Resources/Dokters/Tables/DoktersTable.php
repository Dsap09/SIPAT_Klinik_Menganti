<?php

namespace App\Filament\Resources\Dokters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DoktersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ID_Dokter')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Nama_Dokter')
                    ->label('Nama Dokter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Spesialisasi')
                    ->label('Spesialisasi')
                    ->searchable(),
                TextColumn::make('poli.Nama_Poli')
                    ->label('Poli')
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
