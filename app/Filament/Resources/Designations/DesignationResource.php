<?php

namespace App\Filament\Resources\Designations;

use App\Filament\Resources\Designations\Pages\ListDesignations;
use App\Filament\Resources\Designations\Schemas\DesignationForm;
use App\Filament\Resources\Designations\Tables\DesignationsTable;
use App\Models\Designation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DesignationResource extends Resource
{
    protected static ?string $model = Designation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Recursos Humanos';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Cargos';

    protected static ?string $modelLabel = 'Cargo';

    protected static ?string $pluralModelLabel = 'Cargos';

    public static function form(Schema $schema): Schema
    {
        return DesignationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DesignationsTable::configure($table);
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
            'index' => ListDesignations::route('/'),
        ];
    }
}
