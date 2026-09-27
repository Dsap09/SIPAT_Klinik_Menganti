<?php

namespace App\Filament\Resources\Antreans;

use App\Filament\Resources\Antreans\Pages\CreateAntrean;
use App\Filament\Resources\Antreans\Pages\ListAntreans;
use App\Filament\Resources\Antreans\Schemas\AntreanForm;
use App\Filament\Resources\Antreans\Tables\AntreansTable;
use App\Models\Antrean;
use App\Models\Pengguna;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AntreanResource extends Resource
{
    protected static ?string $model = Antrean::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Loket';

    protected static ?string $navigationLabel = 'Antrean';

    protected static ?string $modelLabel = 'Antrean';

    protected static ?string $pluralModelLabel = 'Antrean';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'No_Antrean';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->Role, [Pengguna::ROLE_PETUGAS, Pengguna::ROLE_ADMIN], true);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pasien', 'jadwal.poli', 'jadwal.dokter'])
            ->padaTanggal(today()->toDateString());
    }

    public static function form(Schema $schema): Schema
    {
        return AntreanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AntreansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAntreans::route('/'),
            'create' => CreateAntrean::route('/create'),
        ];
    }
}
