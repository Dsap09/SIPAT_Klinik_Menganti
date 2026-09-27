<?php

namespace App\Filament\Resources\Antreans\Schemas;

use App\Models\Jadwal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AntreanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ID_Jadwal')
                    ->label('Poli / Jadwal')
                    ->options(fn () => Jadwal::query()
                        ->with(['poli', 'dokter'])
                        ->where('Sisa_Kuota', '>', 0)
                        ->get()
                        ->mapWithKeys(fn (Jadwal $jadwal) => [
                            $jadwal->ID_Jadwal => "{$jadwal->poli->Nama_Poli} — {$jadwal->dokter->Nama_Dokter} — {$jadwal->Hari_Layanan}, "
                                .substr($jadwal->Jam_Mulai, 0, 5).'–'.substr($jadwal->Jam_Selesai, 0, 5)
                                ." (sisa {$jadwal->Sisa_Kuota})",
                        ]))
                    ->searchable()
                    ->required(),
                Radio::make('jenis')
                    ->label('Jenis Pasien')
                    ->options([
                        'BPJS' => 'BPJS (dari Mobile JKN)',
                        'UMUM' => 'Umum (walk-in)',
                    ])
                    ->default('BPJS')
                    ->live()
                    ->required(),
                TextInput::make('No_BPJS')
                    ->label('Nomor BPJS')
                    ->visible(fn (Get $get): bool => $get('jenis') === 'BPJS')
                    ->required(fn (Get $get): bool => $get('jenis') === 'BPJS')
                    ->maxLength(50),
                TextInput::make('Nama_Lengkap')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('Tgl_Lahir')
                    ->label('Tanggal Lahir')
                    ->maxDate(today())
                    ->required(),
                Textarea::make('Alamat')
                    ->label('Alamat')
                    ->columnSpanFull(),
            ]);
    }
}
