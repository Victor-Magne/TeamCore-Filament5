<?php

namespace App\Filament\Resources\LeavesAndAbsences\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeavesAndAbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
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
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date()
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
                    ->native(false),
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
