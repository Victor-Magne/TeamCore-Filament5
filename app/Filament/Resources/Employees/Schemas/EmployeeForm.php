<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\City;
use App\Rules\ValidEmailDomain;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
// AJUSTE AQUI: No Filament 5, componentes de Layout usam o namespace Schema
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Employee Management')
                    ->tabs([
                        // --- TAB 1: IDENTIFICAÇÃO BÁSICA ---
                        Tab::make('Dados Pessoais')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label('Nome')
                                            ->required()
                                            ->maxLength(100),
                                        TextInput::make('last_name')
                                            ->label('Apelido')
                                            ->required()
                                            ->maxLength(100),
                                        TextInput::make('email')
                                            ->label('Email Profissional')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->rule(new ValidEmailDomain),
                                        DatePicker::make('date_of_birth')
                                            ->label('Data de Nascimento')
                                            ->maxDate(now()->subYears(18))
                                            ->required()
                                            ->native(false),
                                        Select::make('gender')
                                            ->label('Género')
                                            ->options([
                                                'male' => 'Masculino',
                                                'female' => 'Feminino',
                                                'other' => 'Outro',
                                            ])
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        // --- TAB 2: DOCUMENTOS E MORADA ---
                        Tab::make('Documentação e Morada')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Section::make('Numero de Telemóvel')
                                    ->schema([
                                        TextInput::make('phone_number')
                                            ->label('Telemóvel')
                                            ->tel()
                                            ->prefix(fn(Get $get) => '+' . (City::find($get('city_id'))?->state?->country?->phonecode ?? ''))
                                            ->required(),
                                    ]),

                                Section::make('Identificação Legal')
                                    ->schema([
                                        TextInput::make('nif')
                                            ->label('NIF')
                                            ->required()
                                            // ->numeric()
                                            ->length(9),
                                        TextInput::make('nss')
                                            ->label('Nº Seg. Social')
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Endereço')
                                    ->schema([
                                        TextInput::make('address')
                                            ->label('Morada Completa')
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('zip_code')
                                            ->label('Código Postal')
                                            ->required()
                                            ->placeholder('0000-000'),
                                        Select::make('city_id')
                                            ->label('Cidade')
                                            ->relationship('city', 'name')
                                            ->searchable()
                                            ->native(false)
                                            ->preload()
                                            ->live()
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        // --- TAB 3: VÍNCULO EMPREGATÍCIO ---
                        Tab::make('Contrato e Empresa')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Select::make('unit_id')
                                            ->label('Unidade/Departamento')
                                            ->relationship('unit', 'name')
                                            ->required()
                                            ->searchable(),
                                        Select::make('designation_id')
                                            ->label('Cargo/Designação')
                                            ->relationship('designation', 'name')
                                            ->searchable(),
                                        DatePicker::make('date_hired')
                                            ->label('Data de Admissão')
                                            ->required(),
                                        DateTimePicker::make('date_dismissed')
                                            ->label('Data de Demissão')
                                            ->helperText('Deixe vazio se estiver ativo'),
                                        TextInput::make('vacation_balance')
                                            ->label('Saldo de Férias')
                                            ->numeric()
                                            ->default(22)
                                            ->required(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
