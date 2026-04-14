<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações Pessoais')
                ->description('Dados básicos do utilizador.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                ])->columns(2),

            Section::make('Autenticação')
                ->description('Definições de segurança.')
                ->schema([
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->confirmed()
                        ->maxLength(255)
                        ->required(fn(string $operation) => $operation === 'create')
                        ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                        ->dehydrated(fn($state) => filled($state)),

                    TextInput::make('password_confirmation')
                        ->label('Confirmar Password')
                        ->password()
                        ->maxLength(255)
                        ->required(fn(string $operation) => $operation === 'create')
                        ->dehydrated(false),

                    Toggle::make('must_change_password')
                        ->label('Forçar alteração de password no próximo login'),
                ])->columns(2),

            Section::make('Associações')
                ->description('Funcionário e roles.')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário Associado')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull(),

                    Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
