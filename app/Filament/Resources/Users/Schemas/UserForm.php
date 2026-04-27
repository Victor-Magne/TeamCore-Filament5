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
            Section::make('AssociaÃ§Ã£o com FuncionÃ¡rio')
                ->description('Selecione o funcionÃ¡rio para importar automaticamente os dados de identidade.')
                ->icon('heroicon-o-identification')
                ->schema([
                    Select::make('employee_id')
                        ->label('FuncionÃ¡rio Correspondente')
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
                        ->helperText('Ao selecionar um funcionÃ¡rio, o sistema preencherÃ¡ o Nome e Email automaticamente.'),
                ]),
            Section::make('Credenciais de Acesso')
                ->description('Configure as informaÃ§Ãµes necessÃ¡rias para o login no sistema.')
                ->icon('heroicon-o-key')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome de ExibiÃ§Ã£o')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex: JoÃ£o Silva'),
                    TextInput::make('email')
                        ->label('EndereÃ§o de E-mail')
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
                                : 'Palavra-passe padrÃ£o: ChangeMe123!'
                        ),
                    Toggle::make('must_change_password')
                        ->label('Exigir alteraÃ§Ã£o de palavra-passe no prÃ³ximo login')
                        ->default(true)
                        ->helperText('Recomenda-se ativar esta opÃ§Ã£o para novos utilizadores.'),
                    Toggle::make('is_active')
                        ->label('Conta Ativa')
                        ->default(true),
                    Select::make('roles')
                        ->label('FunÃ§Ãµes e PermissÃµes (Shield)')
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
