<?php

namespace App\Filament\Resources\LeavesAndAbsences;

use App\Filament\Resources\LeavesAndAbsences\Pages\ListLeavesAndAbsences;
use App\Filament\Resources\LeavesAndAbsences\Schemas\LeaveAndAbsenceForm;
use App\Filament\Resources\LeavesAndAbsences\Tables\LeavesAndAbsencesTable;
use App\Models\LeaveAndAbsence;
use App\Traits\HasHierarchicalQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaveAndAbsenceResource extends Resource
{
    use HasHierarchicalQuery;

    protected static ?string $model = LeaveAndAbsence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Tempo e Frequência';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Licença e Afastamento';

    protected static ?string $navigationLabel = 'Licenças e Afastamentos';

    protected static ?string $pluralModelLabel = 'Licenças e Afastamentos';

    public static function form(Schema $schema): Schema
    {
        return LeaveAndAbsenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeavesAndAbsencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeavesAndAbsences::route('/'),
        ];
    }
}
