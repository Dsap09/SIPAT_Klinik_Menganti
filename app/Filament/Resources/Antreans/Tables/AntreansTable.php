<?php

namespace App\Filament\Resources\Antreans\Tables;

use App\Models\Antrean;
use App\Services\AuditLogger;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AntreansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No_Antrean')
                    ->label('No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pasien.No_RM')
                    ->label('No RM')
                    ->searchable(),
                TextColumn::make('pasien.Nama_Lengkap')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('pasien.Jenis_Pasien')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'BPJS' ? 'info' : 'gray'),
                TextColumn::make('jadwal.poli.Nama_Poli')
                    ->label('Poli')
                    ->badge(),
                TextColumn::make('jadwal.dokter.Nama_Dokter')
                    ->label('Dokter')
                    ->toggleable(),
                TextColumn::make('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Antrean::STATUS_MENUNGGU => 'warning',
                        Antrean::STATUS_DILAYANI => 'info',
                        Antrean::STATUS_SELESAI => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('Estimasi_Waktu')
                    ->label('Estimasi')
                    ->time('H:i'),
                TextColumn::make('Waktu_CheckIn')
                    ->label('Check-in')
                    ->dateTime('H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('No_Antrean')
            ->filters([
                SelectFilter::make('Status')
                    ->options([
                        Antrean::STATUS_MENUNGGU => 'Menunggu',
                        Antrean::STATUS_DILAYANI => 'Dilayani',
                        Antrean::STATUS_SELESAI => 'Selesai',
                        Antrean::STATUS_BATAL => 'Batal',
                    ]),
            ])
            ->recordActions([
                Action::make('checkin')
                    ->label('Check-in')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn (Antrean $record): bool => $record->Waktu_CheckIn === null
                        && $record->Status !== Antrean::STATUS_BATAL)
                    ->modalHeading(fn (Antrean $record): string => "Verifikasi Check-in {$record->No_Antrean}")
                    ->modalDescription('Cocokkan nomor rekam medis (No RM) pasien dengan data pendaftaran.')
                    ->form([
                        TextInput::make('no_rm')
                            ->label('No RM Pasien')
                            ->placeholder('Contoh: RM-2026-0001')
                            ->required(),
                    ])
                    ->action(function (Antrean $record, array $data, Action $action): void {
                        if (strtoupper($record->pasien->No_RM) !== strtoupper(trim((string) $data['no_rm']))) {
                            Notification::make()
                                ->title('Verifikasi gagal')
                                ->body('No RM tidak cocok dengan data pasien.')
                                ->danger()
                                ->send();

                            $action->halt();

                            return;
                        }

                        $record->Waktu_CheckIn = now();
                        $record->save();

                        app(AuditLogger::class)->catat(
                            'Antrean',
                            "Check-in pasien {$record->No_Antrean} ({$record->pasien->Nama_Lengkap})"
                        );
                    })
                    ->successNotificationTitle('Check-in berhasil')
                    ->successRedirectUrl(fn (Antrean $record): string => route('kartu', $record->No_Antrean)),
                Action::make('kartu')
                    ->label('Kartu')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->url(fn (Antrean $record): string => route('kartu', $record->No_Antrean))
                    ->openUrlInNewTab()
                    ->visible(fn (Antrean $record): bool => $record->Waktu_CheckIn !== null),
                Action::make('ubahStatus')
                    ->label('Ubah Status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->modalHeading(fn (Antrean $record): string => "Ubah Status {$record->No_Antrean}")
                    ->form([
                        Select::make('Status')
                            ->options([
                                Antrean::STATUS_MENUNGGU => 'Menunggu',
                                Antrean::STATUS_DILAYANI => 'Dilayani',
                                Antrean::STATUS_SELESAI => 'Selesai',
                                Antrean::STATUS_BATAL => 'Batal',
                            ])
                            ->required(),
                    ])
                    ->fillForm(fn (Antrean $record): array => ['Status' => $record->Status])
                    ->action(function (Antrean $record, array $data): void {
                        $record->Status = $data['Status'];
                        $record->save();

                        app(AuditLogger::class)->catat(
                            'Antrean',
                            "Ubah status {$record->No_Antrean} menjadi {$record->Status}"
                        );
                    })
                    ->successNotificationTitle('Status antrean diperbarui'),
            ]);
    }
}
