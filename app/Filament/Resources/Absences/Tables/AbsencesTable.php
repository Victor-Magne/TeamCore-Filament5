<?php

namespace App\Filament\Resources\Absences\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('absence_date', 'desc')
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('absence_date')
                    ->label('Data da Ausência')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('deduction_type')
                    ->label('Tipo de Dedução')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unjustified_absence' => 'Falta Injustificada',
                        'partial_absence' => 'Falta Parcial',
                        'other' => 'Outra',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unjustified_absence' => 'danger',
                        'partial_absence' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('hours_deducted')
                    ->label('Horas Descontadas')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) return '-';
                        return intdiv($state, 60) . 'h ' . str_pad($state % 60, 2, '0', STR_PAD_LEFT) . 'm';
                    })
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(40)
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
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
