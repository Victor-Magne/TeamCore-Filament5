<?php

namespace App\Filament\Resources\HourBanks\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Schema;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Histórico de Movimentos';

    protected static ?string $modelLabel = 'Movimento';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('date')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'addition' => 'success',
                        'deduction' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'addition' => 'Ganho',
                        'deduction' => 'Desconto',
                        default => $state,
                    }),

                TextColumn::make('amount')
                    ->label('Quantidade')
                    ->formatStateUsing(function (int $state) {
                        $hours = intdiv(abs($state), 60);
                        $minutes = abs($state) % 60;
                        $sign = $state < 0 ? '-' : '+';
                        return "{$sign}{$hours}h {$minutes}m";
                    })
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Movimentos são gerados automaticamente, não permitimos criação manual aqui por agora
            ])
            ->actions([
                // Apenas visualização
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('date', 'desc');
    }
}
