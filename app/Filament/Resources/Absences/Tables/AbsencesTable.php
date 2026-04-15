<?php

namespace App\Filament\Resources\Absences\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('absence_date')
                    ->label('Data da Ausência')
                    ->date('d/m/Y')
                    ->searchable()
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
                        if ($state === null || $state === 0) {
                            return '-';
                        }
                        $hours = intdiv($state, 60);
                        $minutes = $state % 60;

                        return "{$hours}h {$minutes}m";
                    })
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
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
                // Sem EditAction - é apenas visualização
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
