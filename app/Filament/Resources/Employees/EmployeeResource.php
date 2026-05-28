<?php

/**
 * Ficheiro do Resource EmployeeResource.
 *
 * Este recurso é o pilar da gestão de funcionários no painel administrativo do Filament.
 * Define como os funcionários são listados, criados e editados, integrando formulários
 * complexos e tabelas com filtragem avançada.
 */

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\Employees\RelationManagers\HourBankMovementsRelationManager;
use App\Filament\Resources\Employees\RelationManagers\VacationsRelationManager;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EmployeeResource extends Resource
{
    /**
     * Modelo associado ao recurso.
     */
    protected static ?string $model = Employee::class;

    /**
     * Ícone exibido no menu de navegação lateral.
     */
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Identification;

    /**
     * Grupo de navegação no menu lateral para organização.
     */
    protected static string|UnitEnum|null $navigationGroup = 'Recursos Humanos';

    /**
     * Atributo usado para representar o registo em pesquisas globais e títulos.
     */
    protected static ?string $recordTitleAttribute = 'first_name';

    /**
     * Rótulo de navegação traduzido.
     */
    protected static ?string $navigationLabel = 'Funcionários';

    protected static ?string $modelLabel = 'Funcionário';

    protected static ?string $pluralModelLabel = 'Funcionários';

    public static function getNavigationBadge(): ?string
    {
        return (string) Employee::count();
    }

    /**
     * Define a estrutura do formulário de criação e edição.
     *
     * Delega a configuração para a classe especializada EmployeeForm
     * para manter este ficheiro limpo e organizado.
     */
    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    /**
     * Define a estrutura da tabela de listagem.
     *
     * Delega a configuração para a classe especializada EmployeesTable.
     * Inclui carregamento antecipado (Eager Loading) de relações para performance.
     */
    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['unit', 'designation']));
    }

    public static function getRelations(): array
    {
        return [
            ContractsRelationManager::class,
            VacationsRelationManager::class,
            HourBankMovementsRelationManager::class,
        ];
    }

    /**
     * Define as rotas e páginas associadas a este recurso.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    /**
     * Personaliza a query do Eloquent usada para procurar registos por ID nas rotas.
     *
     * Neste caso, garante que registos eliminados (Soft Deleted) continuam
     * acessíveis se referenciados directamente, prevenindo erros de 404 em histórico.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
