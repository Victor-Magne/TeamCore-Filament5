<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Sem funcionários')
            ->emptyStateDescription('Adicione o primeiro funcionário para começar.')
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nome')
                    ->formatStateUsing(fn (Employee $record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone_number')
                    ->label('Telemóvel')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit.name')
                    ->label('Unidade')
                    ->state(fn (Employee $record): string => $record->unit
                        ? collect($record->unit->ancestors)->add($record->unit)->pluck('name')->join(' › ')
                        : '—'
                    )
                    ->searchable(['unit.name'])
                    ->sortable()
                    ->wrap(),
                TextColumn::make('designation.name')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Estado')
                    ->state(fn (Employee $record): bool => is_null($record->date_dismissed))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Employee $record): string => $record->date_dismissed ? 'Inactivo' : 'Activo'),
                TextColumn::make('date_hired')
                    ->label('Data Admissão')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('vacation_balance')
                    ->label('Saldo Férias')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_of_birth')
                    ->label('Data Nascimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nif')
                    ->label('NIF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nss')
                    ->label('Nº Seg. Social')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label('Morada')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('zip_code')
                    ->label('Código Postal')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_dismissed')
                    ->label('Data Demissão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unidade')
                    ->options(function (): array {
                        $user = auth()->user();
                        $query = Unit::withDepth()->defaultOrder();

                        if ($user && ! $user->hasRole('super_admin')) {
                            $employee = $user->employee;
                            if ($employee) {
                                $managedUnits = $employee->getAllManagedUnits();
                                $isGeneralManager = $managedUnits->contains(fn (Unit $u) => $u->isGeneralDirection());

                                if (! $isGeneralManager && $managedUnits->isNotEmpty()) {
                                    $accessibleIds = Unit::where(function (Builder $sub) use ($managedUnits) {
                                        foreach ($managedUnits as $unit) {
                                            $sub->orWhereBetween('_lft', [$unit->_lft, $unit->_rgt]);
                                        }
                                    })->pluck('id');

                                    $query->whereIn('id', $accessibleIds);
                                }
                            }
                        }

                        return $query->get()
                            ->mapWithKeys(fn (Unit $unit) => [
                                $unit->id => str_repeat('— ', $unit->depth).$unit->name,
                            ])
                            ->toArray();
                    })
                    ->searchable(),

                Filter::make('active')
                    ->label('Activos')
                    ->query(fn (Builder $query): Builder => $query->whereNull('date_dismissed'))
                    ->toggle(),

                Filter::make('inactive')
                    ->label('Inactivos')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('date_dismissed'))
                    ->toggle(),

                TrashedFilter::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make('table')
                            ->fromTable()
                            ->except([
                                'created_at',
                                'updated_at',
                                'deleted_at',
                            ]),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
