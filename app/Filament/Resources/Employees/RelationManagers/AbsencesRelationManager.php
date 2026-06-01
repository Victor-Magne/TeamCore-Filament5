<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbsencesRelationManager extends RelationManager
{
    protected static string $relationship = 'absences';

    protected static ?string $title = 'Ausências/Faltas';

    protected static ?string $modelLabel = 'Ausência';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('absence_date', 'desc')
            ->recordTitleAttribute('absence_date')
            ->columns([
                TextColumn::make('absence_date')
                    ->label('Data da Falta')
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
                        default => 'gray',
                    }),

                TextColumn::make('hours_deducted')
                    ->label('Horas Descontadas')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '-';
                        }

                        return intdiv($state, 60).'h '.str_pad($state % 60, 2, '0', STR_PAD_LEFT).'m';
                    }),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
