<?php

namespace App\Filament\Resources\Vacations\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VacationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year_reference')
                    ->label('Ano')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Início')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_taken')
                    ->label('Dias Gozados')
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
