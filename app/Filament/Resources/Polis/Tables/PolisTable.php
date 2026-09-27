<?php

namespace App\Filament\Resources\Polis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PolisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ID_Poli')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Nama_Poli')
                    ->label('Nama Poli')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Deskripsi')
                    ->label('Deskripsi')
                    ->limit(50),
                TextColumn::make('dokter_count')
                    ->label('Jumlah Dokter')
                    ->counts('dokter'),
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
