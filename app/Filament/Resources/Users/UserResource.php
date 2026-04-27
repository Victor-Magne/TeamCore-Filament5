<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canManageRoles(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasRole('super_admin') || $user?->can('Update:Role'));
    }

    public static function getAssignableRolesQuery(): Builder
    {
        $query = Role::query()->orderBy('name');

        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('name', '!=', 'super_admin');
        }

        return $query;
    }

    public static function getAssignableRoleIds(): array
    {
        return self::getAssignableRolesQuery()->pluck('id')->all();
    }

    public static function sanitizeRoleIds(array $roleIds): array
    {
        if (! self::canManageRoles()) {
            return [];
        }

        return array_values(array_intersect($roleIds, self::getAssignableRoleIds()));
    }

    public static function getNavigationLabel(): string
    {
        return __('Utilizadores');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
