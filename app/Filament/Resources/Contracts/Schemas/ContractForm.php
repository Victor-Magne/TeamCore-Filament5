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
                ->icon('heroicon-o-user')
                ->description('Selecione o funcionário e a designação para este contrato.')
                ->columns(2)
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
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
                ]),

            Section::make('Tipo e Estado')
                ->icon('heroicon-o-tag')
                ->description('Defina o tipo e estado do contrato.')
                ->columns(2)
                ->schema([
                    Select::make('type')
                        ->label('Tipo de Contrato')
                        ->options([
                            'permanent' => 'Efetivo / Tempo Indeterminado',
                            'fixed_term' => 'Prazo Certo',
                            'unfixed_term' => 'Prazo Incerto',
                            'internship' => 'Estágio',
                            'service_provision' => 'Prestação de Serviços',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    Select::make('status')
                        ->label('Estado do Contrato')
                        ->options([
                            'active' => 'Ativo',
                            'terminated' => 'Terminado',
                            'on_hold' => 'Suspenso',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),
                ]),

            Section::make('Vigência do Contrato')
                ->icon('heroicon-o-calendar-days')
                ->description('Defina as datas de início e fim.')
                ->columns(2)
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->helperText('Deixe vazio se o contrato for efetivo.')
                        ->native(false)
                        ->hidden(fn (Get $get) => $get('type') === 'permanent')
                        ->after('start_date'),
                ]),

            Section::make('Remuneração e Jornada')
                ->icon('heroicon-o-banknotes')
                ->description('Defina o salário bruto e a jornada diária.')
                ->columns(2)
                ->schema([
                    TextInput::make('salary')
                        ->label('Salário Bruto')
                        ->numeric()
                        ->prefix('€')
                        ->minValue(1)
                        ->required(),

                    TextInput::make('daily_work_minutes')
                        ->label('Jornada Diária (minutos)')
                        ->numeric()
                        ->default(480)
                        ->minValue(60)
                        ->maxValue(600)
                        ->required()
                        ->helperText('Padrão: 480 min (8h). Mín: 60, Máx: 600.'),

                    TimePicker::make('expected_start_time')
                        ->label('Hora de Entrada Esperada')
                        ->default('09:00')
                        ->required()
                        ->native(false),

                    TextInput::make('lunch_duration_minutes')
                        ->label('Duração do Almoço (minutos)')
                        ->numeric()
                        ->default(60)
                        ->minValue(0)
                        ->maxValue(240)
                        ->required()
                        ->helperText('Mín: 0, Máx: 240 minutos.'),
                ]),
        ]);
    }
}
