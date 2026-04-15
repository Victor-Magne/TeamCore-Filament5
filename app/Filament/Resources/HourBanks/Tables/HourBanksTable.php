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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('month_year')
                    ->label('Mês/Ano')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Saldo Total')
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
                    ->sortable(),

                TextColumn::make('extra_hours_added')
                    ->label('Horas Extras Adicionadas')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '-';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('extra_hours_used')
                    ->label('Horas Descontadas')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '-';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('previous_balance')
                    ->label('Saldo Anterior')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '-';
                        }
                        $hours = intdiv(abs($state), 60);
                        $minutes = abs($state) % 60;
                        $sign = $state < 0 ? '-' : '';

                        return "{$sign}{$hours}h {$minutes}m";
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
