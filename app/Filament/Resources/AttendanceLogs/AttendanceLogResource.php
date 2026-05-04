<?php

/**
 * Ficheiro do Resource AttendanceLogResource.
 *
 * Este recurso gere os registos diários de assiduidade dos funcionários.
 * Utiliza o trait HasHierarchicalQuery para garantir que os gestores apenas
 * conseguem visualizar os registos dos seus subordinados directos.
 */

namespace App\Filament\Resources\AttendanceLogs;

use App\Filament\Resources\AttendanceLogs\Pages\CreateAttendanceLog;
use App\Filament\Resources\AttendanceLogs\Pages\EditAttendanceLog;
use App\Filament\Resources\AttendanceLogs\Pages\ListAttendanceLogs;
use App\Filament\Resources\AttendanceLogs\Schemas\AttendanceLogForm;
use App\Filament\Resources\AttendanceLogs\Tables\AttendanceLogsTable;
use App\Models\AttendanceLog;
use App\Traits\HasHierarchicalQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AttendanceLogResource extends Resource
{
    /**
     * Trait para isolamento de dados baseado na hierarquia.
     */
    use HasHierarchicalQuery;

    /**
     * Modelo associado.
     */
    protected static ?string $model = AttendanceLog::class;

    /**
     * Ícone de navegação.
     */
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    /**
     * Agrupamento no menu lateral.
     */
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Tempo e Frequência';

    /**
     * Atributo identificador para pesquisas.
     */
    protected static ?string $recordTitleAttribute = 'id';

    /**
     * Nome traduzido para o menu.
     */
    public static function getNavigationLabel(): string
    {
        return __('Registos de Presença');
    }

    /**
     * Configuração do formulário de edição/criação.
     */
    public static function form(Schema $schema): Schema
    {
        return AttendanceLogForm::configure($schema);
    }

    /**
     * Configuração da tabela de listagem de assiduidade.
     */
    public static function table(Table $table): Table
    {
        return AttendanceLogsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['employee']));
    }

    /**
     * Páginas disponíveis para este recurso.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceLogs::route('/'),
            'create' => CreateAttendanceLog::route('/create'),
            'edit' => EditAttendanceLog::route('/{record}/edit'),
        ];
    }
}
