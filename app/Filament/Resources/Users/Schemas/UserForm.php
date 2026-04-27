<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Associação com Funcionário')
                ->description('Selecione o funcionário para importar automaticamente os dados de identidade.')
                ->icon('heroicon-o-identification')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário Correspondente')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (! $state) {
                                return;
                            }

                            $employee = Employee::find($state);

                            if ($employee) {
                                $set('name', trim("{$employee->first_name} {$employee->last_name}"));
                                $set('email', $employee->email);
                            }
                        })
                        ->helperText('Ao selecionar um funcionário, o sistema preencherá o Nome e Email automaticamente.'),
                ]),
            Section::make('Credenciais de Acesso')
                ->description('Configure as informações necessárias para o login no sistema.')
                ->icon('heroicon-o-key')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome de Exibição')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex: João Silva'),
                    TextInput::make('email')
                        ->label('Endereço de E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('joao.silva@empresa.com'),
                    TextInput::make('password')
                        ->label('Palavra-passe')
                        ->password()
                        ->required(fn (string $context): bool => $context === 'create')
                        ->default(fn (string $context): ?string => $context === 'create' ? 'ChangeMe123!' : null)
                        ->rule(Password::default())
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText(
                            fn (string $context): string => $context === 'edit'
                                ? 'Deixe em branco para manter a palavra-passe atual.'
                                : 'Palavra-passe padrão: ChangeMe123!'
                        ),
                    Toggle::make('must_change_password')
                        ->label('Exigir alteração de palavra-passe no próximo login')
                        ->default(true)
                        ->helperText('Recomenda-se ativar esta opção para novos utilizadores.'),
                    Toggle::make('is_active')
                        ->label('Conta Ativa')
                        ->default(true),
                    Select::make('roles')
                        ->label('Funções e Permissões (Shield)')
                        ->relationship('roles', 'name', modifyQueryUsing: function (Builder $query): Builder {
                            return $query->whereIn('id', UserResource::getAssignableRoleIds());
                        })
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->visible(fn (): bool => UserResource::canManageRoles())
                        ->dehydrated(fn (): bool => UserResource::canManageRoles())
                        ->suffixIcon('heroicon-m-shield-check'),
                ]),
        ];
    }
}
