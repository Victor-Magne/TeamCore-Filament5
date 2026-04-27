<?php

namespace App\Filament\Resources\Designations\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DesignationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('level')
                    ->label('Nível')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('role_name')
                    ->label('Role Associada')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('base_salary')
                    ->label('Salário Base')
                    ->money('EUR') // Mude para a sua moeda (USD, AOA, BRL, etc.)
                    ->sortable(),

                TextColumn::make('employees_count')
                    ->label('Ocupação')
                    ->counts('employees')
                    ->badge()
                    ->color('info')
                    ->suffix(' colaboradores'),
            ])
            ->filters([
                // Filtros podem ser adicionados aqui depois
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
