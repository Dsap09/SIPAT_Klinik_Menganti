<?php

namespace App\Filament\Resources\Jadwals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JadwalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ID_Jadwal')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('poli.Nama_Poli')
                    ->label('Poli')
                    ->badge()
                    ->sortable(),
                TextColumn::make('dokter.Nama_Dokter')
                    ->label('Dokter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Hari_Layanan')
                    ->label('Hari')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Jam_Mulai')
                    ->label('Mulai')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('Jam_Selesai')
                    ->label('Selesai')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('Kuota_Maksimal')
                    ->label('Kuota')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('Sisa_Kuota')
                    ->label('Sisa')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->defaultSort('Hari_Layanan')
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
