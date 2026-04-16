<?php

namespace App\Filament\Resources\Payrolls\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month_year')
                    ->label('Referência')
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label('Salário Base')
                    ->money('EUR'),
                TextColumn::make('total_net')
                    ->label('Valor Líquido')
                    ->money('EUR')
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
