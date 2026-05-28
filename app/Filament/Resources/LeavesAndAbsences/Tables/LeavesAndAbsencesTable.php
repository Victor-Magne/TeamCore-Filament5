<?php

namespace App\Filament\Resources\LeavesAndAbsences\Tables;

use App\Models\Employee;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class LeavesAndAbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sick_leave' => 'Baixa Médica',
                        'parental' => 'Lic. Parental',
                        'marriage' => 'Casamento',
                        'bereavement' => 'Nojo',
                        'justified_absence' => 'Falta Justif.',
                        'unjustified' => 'Falta Injustif.',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sick_leave' => 'danger',
                        'parental' => 'info',
                        'marriage' => 'primary',
                        'bereavement' => 'warning',
                        'justified_absence' => 'success',
                        'unjustified' => 'secondary',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label('Remunerado')
                    ->boolean(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        ! auth()->user()?->can('Approve:OwnLeaveAndAbsence')
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

                Filter::make('date_range')
                    ->label('Período')
                    ->form([
                        DatePicker::make('from')
                            ->label('De')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Até')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('start_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('start_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'De: ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Até: ' . $data['until'];
                        }
                        return $indicators;
                    }),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'sick_leave' => 'Baixa Médica (SNS)',
                        'parental' => 'Licença Parental',
                        'marriage' => 'Licença de Casamento',
                        'bereavement' => 'Nojo (Falecimento)',
                        'justified_absence' => 'Falta Justificada',
                        'unjustified' => 'Falta Injustificada',
                    ]),

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
