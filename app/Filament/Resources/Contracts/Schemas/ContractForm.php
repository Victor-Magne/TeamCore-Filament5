<?php

namespace App\Filament\Resources\Contracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário')
                ->description('Selecione o funcionário para este contrato.')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Select::make('designation_id')
                        ->label('Designação')
                        ->relationship('designation', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Tipo de Contrato')
                ->description('Defina o tipo e estado do contrato.')
                ->schema([
                    Select::make('type')
                        ->label('Tipo de Contrato')
                        ->options([
                            'permanent' => 'Efetivo / Tempo Indeterminado',
                            'fixed-term' => 'Prazo Certo',
                            'internship' => 'Estágio',
                            'freelance' => 'Prestação de Serviços',
                        ])
                        ->required()
                        ->native(false)
                        ->live()
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),

                    Select::make('status')
                        ->label('Estado do Contrato')
                        ->options([
                            'active' => 'Ativo',
                            'terminated' => 'Terminado',
                            'on_hold' => 'Suspenso',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false)
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),
                ])
                ->extraAttributes([
                    'class' => 'flex flex-wrap gap-4',
                ]),

            Section::make('Vigência do Contrato')
                ->description('Defina as datas de início e fim.')
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false)
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->helperText('Deixe vazio se o contrato for efetivo.')
                        ->native(false)
                        ->hidden(fn(Get $get) => $get('type') === 'permanent')
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),
                ])
                ->extraAttributes([
                    'class' => 'flex flex-wrap gap-4',
                ]),

            Section::make('Remuneração e Jornada')
                ->description('Defina o salário bruto e a jornada diária.')
                ->schema([
                    TextInput::make('salary')
                        ->label('Salário Bruto')
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),

                    TextInput::make('daily_work_minutes')
                        ->label('Jornada Diária (minutos)')
                        ->numeric()
                        ->default(480)
                        ->required()
                        ->helperText('Padrão: 480 minutos (8 horas)')
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),

                    TimePicker::make('expected_start_time')
                        ->label('Hora de Entrada Esperada')
                        ->default('09:00')
                        ->required()
                        ->native(false)
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),

                    TextInput::make('lunch_duration_minutes')
                        ->label('Duração do Almoço (minutos)')
                        ->numeric()
                        ->default(60)
                        ->required()
                        ->extraAttributes([
                            'class' => 'flex-1',
                        ]),
                ])
                ->extraAttributes([
                    'class' => 'flex flex-wrap gap-4',
                ]),
        ]);
    }
}
