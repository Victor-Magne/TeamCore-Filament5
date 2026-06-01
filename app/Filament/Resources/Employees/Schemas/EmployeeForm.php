<?php

/**
 * Ficheiro de Configuração do Formulário de Funcionários.
 *
 * Esta classe isola a lógica de construção do formulário do Resource Employee.
 * Utiliza o sistema de Schemas do Filament 5 para organizar os campos em Tabs
 * e Sections, garantindo uma interface de utilizador limpa para lidar com
 * a grande quantidade de dados de um funcionário.
 */

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\City;
use App\Rules\ValidEmailDomain;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EmployeeForm
{
    /**
     * Configura os componentes do formulário de funcionário.
     *
     * @param  Schema  $schema  O objecto de schema a ser configurado.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Organização em Tabs para melhor experiência de utilizador (UX)
                Tabs::make('Gestão de Funcionário')
                    ->tabs([
                        // --- TAB 1: IDENTIFICAÇÃO BÁSICA ---
                        Tab::make('Dados Pessoais')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Section::make('Informação Pessoal')
                                    ->icon('heroicon-o-user-circle')
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
                                            ->unique(ignoreRecord: true) // Evita erro de e-mail duplicado ao editar o próprio registo
                                            ->rule(new ValidEmailDomain), // Validação customizada para garantir TLD válido (.pt, .com, etc)
                                        DatePicker::make('date_of_birth')
                                            ->label('Data de Nascimento')
                                            ->maxDate(now()->subYears(18)) // Só permite maiores de 18 anos
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
                                Section::make('Contacto Telefónico')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        TextInput::make('phone_number')
                                            ->label('Telemóvel')
                                            ->tel()
                                            // Dinamicamente obtém o indicativo do país baseado na cidade seleccionada
                                            ->prefix(fn (Get $get) => '+'.(City::find($get('city_id'))?->state?->country?->phonecode ?? ''))
                                            ->required(),
                                    ]),

                                Section::make('Identificação Legal')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        TextInput::make('nif')
                                            ->label('NIF')
                                            ->required()
                                            ->length(9), // NIF em Portugal tem sempre 9 dígitos
                                        TextInput::make('nss')
                                            ->label('Nº Seg. Social')
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Endereço')
                                    ->icon('heroicon-o-map-pin')
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
                                            ->live() // Permite que outros campos reajam à mudança deste
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        // --- TAB 3: VÍNCULO EMPREGATÍCIO ---
                        Tab::make('Contrato e Empresa')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make('Vínculo Laboral')
                                    ->icon('heroicon-o-briefcase')
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
                                            ->maxDate(Carbon::now()) // Não permite datas futuras
                                            ->native(false)
                                            ->required(),
                                        DatePicker::make('date_dismissed')
                                            ->label('Data de Demissão')
                                            ->minDate(fn (Get $get) => $get('date_hired')) // Não permite demissão antes da admissão
                                            ->native(false)
                                            ->helperText('Deixe vazio se o funcionário estiver activo na empresa'),
                                        TextInput::make('vacation_balance')
                                            ->label('Saldo de Férias')
                                            ->numeric()
                                            ->default(22) // Em Portugal, o mínimo legal são 22 dias úteis
                                            ->required(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
