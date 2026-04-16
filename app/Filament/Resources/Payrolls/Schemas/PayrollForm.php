<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable(),
                        TextInput::make('month_year')
                            ->placeholder('YYYY-MM')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->validationMessages([
                                'regex' => 'O formato deve ser YYYY-MM (ex: 2024-01).',
                            ])
                            ->required(),
                        TextInput::make('base_salary')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        TextInput::make('extra_hours_amount')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        TextInput::make('deductions')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        TextInput::make('total_net')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
