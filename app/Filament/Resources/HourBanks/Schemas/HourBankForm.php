<?php

namespace App\Filament\Resources\HourBanks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
// AJUSTE AQUI: No Filament 5, componentes de Layout usam o namespace Schema
use Filament\Schemas\Schema;

class HourBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações do Banco de Horas')
                ->description('Visualização do saldo mensal de horas extras.')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('month_year')
                        ->label('Mês/Ano (YYYY-MM)')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Saldos')
                ->schema([
                    TextInput::make('balance')
                        ->label('Saldo Total (minutos)')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->helperText('Positivo = crédito | Negativo = débito'),

                    TextInput::make('extra_hours_added')
                        ->label('Horas Extras Adicionadas (min)')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('extra_hours_used')
                        ->label('Horas Descontadas (min)')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('previous_balance')
                        ->label('Saldo Anterior (min)')
                        ->numeric()
                        ->default(0)
                        ->required(),
        ])->columns(2),
        ]);
    }
}
