<?php

namespace App\Filament\App\Widgets;

use App\Models\Vacation;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class EmployeeVacationWidget extends BaseWidget
{
    protected static ?string $heading = 'Minhas Férias';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = Auth::user()->employee;

        return $table
            ->query(
                Vacation::where('employee_id', $employee?->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('days_taken')
                    ->label('Dias'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        default => $state,
                    }),
            ])
            ->headerActions([
                Action::make('balance')
                    ->label(fn () => 'Saldo Restante: '.($employee?->vacation_balance ?? 0).' dias')
                    ->button()
                    ->disabled()
                    ->color('info'),
            ]);
    }
}
