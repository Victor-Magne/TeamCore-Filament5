<?php

namespace App\Filament\Resources\Payrolls\Tables;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                Action::make('mark_as_paid')
                    ->label('Pagar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->action(fn ($record) => $record->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_paid_bulk')
                        ->label('Marcar como Pagos')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
