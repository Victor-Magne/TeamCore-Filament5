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
            Section::make('Funcionário')
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
                ]),

            Section::make('Saldos Acumulados')
                ->columns(3)
                ->schema([
                    TextInput::make('balance')
                        ->label('Saldo Actual')
                        ->disabled()
                        ->formatStateUsing(function (?int $state): string {
                            if ($state === null) return '-';
                            $sign = $state < 0 ? '-' : '';
                            $abs = abs($state);
                            return "{$sign}" . intdiv($abs, 60) . 'h ' . ($abs % 60) . 'm';
                        })
                        ->helperText('Positivo = crédito | Negativo = débito'),

                    TextInput::make('extra_hours_added')
                        ->label('Total Ganhos')
                        ->disabled()
                        ->formatStateUsing(function (?int $state): string {
                            if ($state === null || $state === 0) return '0h 00m';
                            return intdiv($state, 60) . 'h ' . str_pad($state % 60, 2, '0', STR_PAD_LEFT) . 'm';
                        }),

                    TextInput::make('extra_hours_used')
                        ->label('Total Descontos')
                        ->disabled()
                        ->formatStateUsing(function (?int $state): string {
                            if ($state === null || $state === 0) return '0h 00m';
                            return intdiv($state, 60) . 'h ' . str_pad($state % 60, 2, '0', STR_PAD_LEFT) . 'm';
                        }),
                ]),
        ]);
    }
}
