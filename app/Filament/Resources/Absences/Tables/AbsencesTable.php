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

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'falta' => 'danger',
                        'atraso' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'justificado' => 'success',
                        'pendente' => 'warning',
                        'rejeitado' => 'danger',
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
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
