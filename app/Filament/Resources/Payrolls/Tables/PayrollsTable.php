<?php

namespace App\Filament\Resources\Payrolls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make('table')
                            ->fromTable()
                            ->except([
                                'created_at',
                                'updated_at',
                            ]),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
