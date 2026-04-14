<?php

namespace App\Filament\Resources\Contracts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('salary')
                    ->label('Salário')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'terminated' => 'danger',
                        'on_hold' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date()
                    ->placeholder('Indeterminado'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Ativos',
                        'terminated' => 'Terminados',
                        'on_hold' => 'Suspensos',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo de Contrato')
                    ->options([
                        'permanent' => 'Efetivo',
                        'fixed-term' => 'Prazo Certo',
                        'internship' => 'Estágio',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
