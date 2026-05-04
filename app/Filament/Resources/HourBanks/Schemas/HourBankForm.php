<?php

namespace App\Filament\Resources\HourBanks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HourBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações do Banco de Horas')
                ->description('Visualização do saldo acumulado do colaborador.')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabledOn('edit')
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Saldos Acumulados')
                ->schema([
                    TextInput::make('balance')
                        ->label('Saldo Actual (minutos)')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->helperText('Positivo = crédito | Negativo = débito'),

                    TextInput::make('extra_hours_added')
                        ->label('Total Ganhos (min)')
                        ->numeric()
                        ->default(0)
                        ->disabled(),

                    TextInput::make('extra_hours_used')
                        ->label('Total Descontos (min)')
                        ->numeric()
                        ->default(0)
                        ->disabled(),
        ])->columns(3),
        ]);
    }
}
