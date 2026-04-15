<?php

namespace App\Filament\Resources\AttendanceLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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

            Section::make('Horários do Ponto')
                ->description('Registar os 4 momentos do dia: entrada, saída para almoço, volta do almoço e fim do expediente.')
                ->schema([
                    DateTimePicker::make('time_in')
                        ->label('Entrada')
                        ->required()
                        ->native(false),

                    DateTimePicker::make('lunch_break_start')
                        ->label('Saída para Almoço')
                        ->native(false),

                    DateTimePicker::make('lunch_break_end')
                        ->label('Volta do Almoço')
                        ->native(false),

                    DateTimePicker::make('time_out')
                        ->label('Fim do Expediente')
                        ->native(false),
                ])->columns(2),

            Section::make('Tempo Total')
                ->schema([
                    TextInput::make('total_minutes')
                        ->label('Tempo Total (minutos)')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function ($record) {
                            if (! $record?->total_minutes) {
                                return 'Será calculado automaticamente';
                            }

                            $hours = intdiv($record->total_minutes, 60);
                            $minutes = $record->total_minutes % 60;

                            return "{$hours}h {$minutes}m";
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notas')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
