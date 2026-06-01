<?php

namespace App\Filament\Resources\Payrolls\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('month_year', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Sem processamentos')
            ->emptyStateDescription('Use o botão "Processar Salários" para gerar os processamentos mensais.')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
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
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
            ])
            ->filters([
                Filter::make('month_year')
                    ->label('Mês de Referência')
                    ->form([
                        TextInput::make('month_year')
                            ->label('Mês (AAAA-MM)')
                            ->placeholder(now()->format('Y-m'))
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['month_year'], fn (Builder $q, $v) => $q->where('month_year', $v))
                    )
                    ->indicateUsing(fn (array $data): ?string => $data['month_year']
                        ? 'Referência: '.$data['month_year']
                        : null
                    ),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->recordActions([
                Action::make('mark_as_paid')
                    ->label('Marcar como Pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'paid'])),
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
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_paid_bulk')
                        ->label('Marcar como Pagos')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'paid'])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
