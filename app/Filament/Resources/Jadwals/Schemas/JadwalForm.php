<?php

namespace App\Filament\Resources\Jadwals\Schemas;

use App\Models\Dokter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class JadwalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ID_Poli')
                    ->label('Poli')
                    ->relationship('poli', 'Nama_Poli')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('ID_Dokter')
                    ->label('Dokter')
                    ->options(fn (Get $get) => Dokter::query()
                        ->when($get('ID_Poli'), fn ($query, $poli) => $query->where('ID_Poli', $poli))
                        ->orderBy('Nama_Dokter')
                        ->pluck('Nama_Dokter', 'ID_Dokter'))
                    ->searchable()
                    ->required(),
                Select::make('Hari_Layanan')
                    ->label('Hari Layanan')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                        'Minggu' => 'Minggu',
                    ])
                    ->required(),
                TimePicker::make('Jam_Mulai')
                    ->label('Jam Mulai')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('Jam_Selesai')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->required(),
                TextInput::make('Kuota_Maksimal')
                    ->label('Kuota Maksimal')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('Sisa_Kuota')
                    ->label('Sisa Kuota')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Kosongkan untuk mengikuti kuota maksimal.'),
            ]);
    }
}
