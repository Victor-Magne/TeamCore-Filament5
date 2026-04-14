<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('ISO')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('phonecode')
                    ->label('Indicativo')
                    ->prefix('+'),
                TextColumn::make('states_count')
                    ->label('Estados/Províncias')
                    ->counts('states')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
