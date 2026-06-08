<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class AssignRoleBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'assign_role';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Atribuir Role')
            ->icon('heroicon-o-shield-check')
            ->schema([
                Select::make('roles')
                    ->label('Roles')
                    ->options(Role::orderBy('name')->pluck('name', 'name'))
                    ->multiple()
                    ->required()
                    ->searchable(),
            ])
            ->action(function (Collection $records, array $data): void {
                $records->each->syncRoles($data['roles']);
            })
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Roles atribuídos com sucesso');
    }
}
