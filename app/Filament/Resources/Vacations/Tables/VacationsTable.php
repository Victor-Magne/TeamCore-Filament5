<?php

namespace App\Filament\Resources\Vacations\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class VacationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year_reference')
                    ->label('Ano')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Início')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date()
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
                    ->disabled(fn ($record): bool =>
                        $record->employee_id === auth()->user()?->employee_id &&
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
                SelectFilter::make('status')
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
