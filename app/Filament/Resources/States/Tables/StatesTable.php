<?php

namespace App\Filament\Resources\States\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Estado/Província')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country.name')
                    ->label('País')
                    ->badge()
                    ->sortable(),
                TextColumn::make('cities_count')
                    ->label('Cidades')
                    ->counts('cities')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Filtrar por País')
                    ->relationship('country', 'name'),
            ]);
    }
}
