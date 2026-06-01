<?php

namespace App\Filament\Resources\Vacations;

use App\Filament\Resources\Vacations\Pages\ListVacations;
use App\Filament\Resources\Vacations\Schemas\VacationForm;
use App\Filament\Resources\Vacations\Tables\VacationsTable;
use App\Models\Vacation;
use App\Traits\HasHierarchicalQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VacationResource extends Resource
{
    use HasHierarchicalQuery;

    protected static ?string $model = Vacation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Tempo e Frequência';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Férias';

    protected static ?string $modelLabel = 'Férias';

    protected static ?string $pluralModelLabel = 'Férias';

    public static function getNavigationBadge(): ?string
    {
        return ($count = Vacation::where('status', 'pending')->count()) > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return VacationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacations::route('/'),
        ];
    }
}
