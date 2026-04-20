<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Employee;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Forms;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Toggle; // ADICIONA ISTO
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
            // SECÇÃO 1: VÍNCULO COM O RH
            Section::make('Associação com Funcionário')
                ->description('Selecione o funcionário para importar automaticamente os dados de identidade.')
                ->icon('heroicon-o-identification')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário Correspondente')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        /* * ATENÇÃO: O live() é o que permite que o Filament 
                         * "viva" e reaja a cada alteração sem recarregar a página.
                         */
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (!$state) {
                                return;
                            }

                            $employee = Employee::find($state);

                            if ($employee) {
                                // Preenche o Nome (Primeiro + Último) e o Email da ficha de funcionário
                                $set('name', trim("{$employee->first_name} {$employee->last_name}"));
                                $set('email', $employee->email);
                            }
                        })
                        ->helperText('Ao selecionar um funcionário, o sistema preencherá o Nome e Email automaticamente.'),
                ]),

            // SECÇÃO 2: DADOS DE ACESSO
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
                        // Apenas obrigatória na criação de um novo utilizador
                        ->required(fn(string $context): bool => $context === 'create')
                        // Palavra-passe padrão na criação
                        ->default(fn(string $context): ?string => $context === 'create' ? 'ChangeMe123!' : null)
                        // Regras de segurança (ex: mín. 8 caracteres)
                        ->rule(Password::default())
                        // Não guarda se estiver vazio (útil na edição)
                        ->dehydrated(fn($state) => filled($state))
                        // Faz o Hash automático antes de enviar para a base de dados
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->helperText(
                            fn(string $context): string =>
                            $context === 'edit' ? 'Deixe em branco para manter a palavra-passe atual.' : 'Palavra-passe padrão: ChangeMe123!'
                        ),

                    Toggle::make('must_change_password')
                        ->label('Exigir alteração de palavra-passe no próximo login')
                        ->default(true)
                        ->helperText('Recomenda-se ativar esta opção para novos utilizadores.'),

                    Toggle::make('is_active') // Substitui 'is_active' pelo nome correto da tua coluna na base de dados
                        ->label('Conta Ativa')
                        ->default(true),

                    Select::make('roles')
                        ->label('Funções e Permissões (Shield)')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        //->required()
                        ->suffixIcon('heroicon-m-shield-check'),
                ]),
        ];
    }
}
