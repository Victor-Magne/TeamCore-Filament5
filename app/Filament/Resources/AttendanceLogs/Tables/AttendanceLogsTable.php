<?php

namespace App\Filament\Resources\AttendanceLogs\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class AttendanceLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('time_in', 'desc')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('time_in')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i')
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
            ->filters([
                Filter::make('date_range')
                    ->label('Período')
                    ->form([
                        DatePicker::make('from')
                            ->label('De')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Até')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('time_in', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('time_in', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'De: ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Até: ' . $data['until'];
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
