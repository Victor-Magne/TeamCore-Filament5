<?php

namespace App\Filament\Resources\Vacations\Tables;

use App\Models\Employee;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class VacationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-sun')
            ->emptyStateHeading('Sem férias registadas')
            ->emptyStateDescription('Registe os pedidos de férias dos colaboradores.')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('year_reference')
                    ->label('Ano')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('days_taken')
                    ->label('Dias Gozados')
                    ->numeric(),
                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                    ])
                    ->native(false)
                    ->disabled(fn ($record): bool => $record->employee_id === auth()->user()?->employee_id &&
                        ! auth()->user()?->can('Approve:OwnVacation')
                    ),
                TextColumn::make('approver.name')
                    ->label('Aprovado Por')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rejection_reason')
                    ->label('Razão da Rejeição')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->options(fn () => Employee::query()
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}"])
                        ->toArray()
                    )
                    ->searchable(),

                Filter::make('year_reference')
                    ->label('Ano')
                    ->form([
                        TextInput::make('year')
                            ->label('Ano')
                            ->numeric()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(2100),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['year'], fn (Builder $q, $v) => $q->where('year_reference', $v))
                    )
                    ->indicateUsing(fn (array $data): ?string => $data['year']
                        ? 'Ano: '.$data['year']
                        : null
                    ),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                    ]),
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
