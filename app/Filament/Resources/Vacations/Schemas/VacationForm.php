<?php

namespace App\Filament\Resources\Vacations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('Período de Férias')
                ->description('Defina as datas e dias gozados.')
                ->schema([
                    TextInput::make('year_reference')
                        ->label('Ano de Referência')
                        ->numeric()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->required()
                        ->native(false),

                    TextInput::make('days_taken')
                        ->label('Dias Gozados')
                        ->numeric()
                        ->required(),
                ])->columns(2),

            Section::make('Aprovação')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendente',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false),

                    Select::make('approved_by')
                        ->label('Aprovado por')
                        ->relationship('approver', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ])->columns(2),
        ]);
    }
}
