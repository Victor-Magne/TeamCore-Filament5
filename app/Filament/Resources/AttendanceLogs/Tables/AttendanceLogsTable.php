<?php

namespace App\Filament\Resources\AttendanceLogs\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('time_in')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->sortable(),

                TextColumn::make('lunch_break_start')
                    ->label('Saída Almoço')
                    ->dateTime('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lunch_break_end')
                    ->label('Volta Almoço')
                    ->dateTime('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('time_out')
                    ->label('Fim Expediente')
                    ->dateTime('H:i'),

                TextColumn::make('total_minutes')
                    ->label('Total')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null) {
                            return '-';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
