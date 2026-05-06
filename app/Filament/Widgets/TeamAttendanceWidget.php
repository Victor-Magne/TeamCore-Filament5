<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TeamAttendanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Assiduidade da Equipa (Hoje)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $meuEmployee = $user?->employee;

        if (! $meuEmployee) {
            return $table->query(Employee::whereRaw('1=0'));
        }

        $employeeIds = $meuEmployee->getAllSubordinateEmployeeIds();

        return $table
            ->query(
                Employee::whereIn('id', $employeeIds)
                    ->with(['attendanceLogs' => function ($query) {
                        $query->whereDate('time_in', Carbon::today());
                    }, 'unit'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Colaborador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unidade'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado Atual')
                    ->getStateUsing(function (Employee $record) {
                        $log = $record->attendanceLogs->first();
                        if (! $log) {
                            return 'Não Iniciou';
                        }

                        if ($log->time_out) {
                            return 'Saída';
                        }
                        if ($log->lunch_end) {
                            return 'Trabalho (Pós-Almoço)';
                        }
                        if ($log->lunch_start) {
                            return 'Em Pausa';
                        }
                        if ($log->time_in) {
                            return 'Trabalho (Manhã)';
                        }

                        return 'Desconhecido';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Trabalho (Manhã)', 'Trabalho (Pós-Almoço)' => 'success',
                        'Em Pausa' => 'warning',
                        'Saída' => 'gray',
                        'Não Iniciou' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('attendanceLogs.time_in')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->placeholder('--:--'),
                Tables\Columns\TextColumn::make('attendanceLogs.time_out')
                    ->label('Saída')
                    ->dateTime('H:i')
                    ->placeholder('--:--'),
            ]);
    }
}
