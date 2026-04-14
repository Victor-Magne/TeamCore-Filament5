<?php

namespace App\Filament\Resources\AttendanceLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class AttendanceLogForm
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

            Section::make('Registo')
                ->description('Defina o tipo e hora do registo.')
                ->schema([
                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'check_in' => 'Entrada',
                            'check_out' => 'Saída',
                        ])
                        ->required()
                        ->native(false),

                    DateTimePicker::make('recorded_at')
                        ->label('Data e Hora')
                        ->required()
                        ->native(false),
                ])->columns(2),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notas')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
