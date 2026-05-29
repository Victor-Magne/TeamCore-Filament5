<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label('Estado/Província')
                    ->sortable(),
                TextColumn::make('state.country.name')
                    ->label('País')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('state_id')
                    ->label('Filtrar por Estado')
                    ->relationship('state', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
