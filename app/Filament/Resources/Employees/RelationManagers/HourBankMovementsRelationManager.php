<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HourBankMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'hourBankMovements';

    protected static ?string $title = 'Banco de Horas';

    protected static ?string $modelLabel = 'Movimento';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'addition' => 'Ganho',
                        'deduction' => 'Desconto',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'addition' => 'success',
                        'deduction' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Quantidade')
                    ->formatStateUsing(function (int $state): string {
                        $abs = abs($state);
                        $sign = $state < 0 ? '-' : '+';
                        return $sign . intdiv($abs, 60) . 'h ' . str_pad($abs % 60, 2, '0', STR_PAD_LEFT) . 'm';
                    })
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->searchable(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
