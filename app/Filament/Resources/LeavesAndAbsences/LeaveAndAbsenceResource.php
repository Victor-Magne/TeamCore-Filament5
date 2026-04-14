<?php

namespace App\Filament\Resources\LeavesAndAbsences;

use App\Filament\Resources\LeavesAndAbsences\Pages\CreateLeaveAndAbsence;
use App\Filament\Resources\LeavesAndAbsences\Pages\EditLeaveAndAbsence;
use App\Filament\Resources\LeavesAndAbsences\Pages\ListLeavesAndAbsences;
use App\Filament\Resources\LeavesAndAbsences\Schemas\LeaveAndAbsenceForm;
use App\Filament\Resources\LeavesAndAbsences\Tables\LeavesAndAbsencesTable;
use App\Models\LeaveAndAbsence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaveAndAbsenceResource extends Resource
{
    protected static ?string $model = LeaveAndAbsence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Pessoal';

    protected static ?string $recordTitleAttribute = 'id';

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
            'create' => CreateLeaveAndAbsence::route('/create'),
            'edit' => EditLeaveAndAbsence::route('/{record}/edit'),
        ];
    }
}
