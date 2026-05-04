<?php

namespace App\Filament\Resources\HourBanks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class HourBanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Saldo Acumulado')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null) {
                            return '-';
                        }
                        $hours = intdiv(abs($state), 60);
                        $minutes = abs($state) % 60;
                        $sign = $state < 0 ? '-' : '';

                        return "{$sign}{$hours}h {$minutes}m";
                    })
                    ->color(fn (?int $state) => $state === null ? 'gray' : ($state >= 0 ? 'success' : 'danger'))
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('extra_hours_added')
                    ->label('Total de Ganhos')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '0h 00m';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable(),

                TextColumn::make('extra_hours_used')
                    ->label('Total de Descontos')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '0h 00m';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Última Actualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
