<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class EmployeeAbsencesWidget extends BaseWidget
{
    protected static ?string $heading = 'Minhas Faltas';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = Auth::user()->employee;

        return $table
            ->query(
                Absence::where('employee_id', $employee?->id)
                    ->latest('absence_date')
            )
            ->paginated([5, 10])
            ->columns([
                Tables\Columns\TextColumn::make('absence_date')
                    ->label('Data da Falta')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('deduction_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unjustified_absence' => 'Falta Injustificada',
                        'partial_absence' => 'Falta Parcial',
                        'other' => 'Outra',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unjustified_absence' => 'danger',
                        'partial_absence' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('hours_deducted')
                    ->label('Horas Descontadas')
                    ->formatStateUsing(function (?int $state): string {
                        if (! $state) {
                            return '-';
                        }

                        return intdiv($state, 60).'h '.str_pad($state % 60, 2, '0', STR_PAD_LEFT).'m';
                    }),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->emptyStateHeading('Sem faltas registadas')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
