<?php

namespace App\Filament\Resources\HourBanks;

use App\Filament\Resources\HourBanks\Pages\EditHourBank;
use App\Filament\Resources\HourBanks\Pages\ListHourBanks;
use App\Filament\Resources\HourBanks\Schemas\HourBankForm;
use App\Filament\Resources\HourBanks\Tables\HourBanksTable;
use App\Models\HourBank;
use App\Traits\HasHierarchicalQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HourBankResource extends Resource
{
    use HasHierarchicalQuery;

    protected static ?string $model = HourBank::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Tempo e Frequência';

    protected static ?string $navigationLabel = 'Banco de Horas';

    protected static ?string $modelLabel = 'Banco de Horas';

    protected static ?string $pluralModelLabel = 'Bancos de Horas';

    public static function form(Schema $schema): Schema
    {
        return HourBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HourBanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHourBanks::route('/'),
            'edit' => EditHourBank::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
