<?php

namespace App\Filament\Resources\Absences;

use App\Filament\Resources\Absences\Pages\EditAbsence;
use App\Filament\Resources\Absences\Pages\ListAbsences;
use App\Filament\Resources\Absences\Schemas\AbsenceForm;
use App\Filament\Resources\Absences\Tables\AbsencesTable;
use App\Models\Absence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::XMark;

    protected static ?string $navigationLabel = 'Ausências/Faltas';

    protected static ?string $modelLabel = 'Ausência/Falta';

    protected static ?string $pluralModelLabel = 'Ausências/Faltas';

    public static function form(Schema $schema): Schema
    {
        return AbsenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsencesTable::configure($table);
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
            'index' => ListAbsences::route('/'),
            'edit' => EditAbsence::route('/{record}/edit'),
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
