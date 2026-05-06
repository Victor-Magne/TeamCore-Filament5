<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeamPendingRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pedidos Pendentes da Equipa';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $meuEmployee = $user?->employee;

        if (!$meuEmployee) {
            return $table->query(Vacation::whereRaw('1=0'));
        }

        $employeeIds = $meuEmployee->getAllSubordinateEmployeeIds();

        // Subquery 1: Vacations
        $vacationQuery = DB::table('vacations')
            ->join('employees', 'vacations.employee_id', '=', 'employees.id')
            ->select([
                'vacations.id',
                'vacations.employee_id',
                'vacations.start_date',
                'vacations.end_date',
                'vacations.status',
                'employees.first_name',
                'employees.last_name',
                DB::raw("'Férias' as request_type"),
                DB::raw("'App\\\\Models\\\\Vacation' as model_type"),
                DB::raw("CONCAT('Vacation_', vacations.id) as row_key")
            ])
            ->whereIn('vacations.employee_id', $employeeIds)
            ->where('vacations.status', 'pending')
            ->whereNull('vacations.deleted_at');

        // Subquery 2: Leaves
        $leaveQuery = DB::table('leaves_and_absences')
            ->join('employees', 'leaves_and_absences.employee_id', '=', 'employees.id')
            ->select([
                'leaves_and_absences.id',
                'leaves_and_absences.employee_id',
                'leaves_and_absences.start_date',
                'leaves_and_absences.end_date',
                'leaves_and_absences.status',
                'employees.first_name',
                'employees.last_name',
                DB::raw("leaves_and_absences.type as request_type"),
                DB::raw("'App\\\\Models\\\\LeaveAndAbsence' as model_type"),
                DB::raw("CONCAT('Leave_', leaves_and_absences.id) as row_key")
            ])
            ->whereIn('leaves_and_absences.employee_id', $employeeIds)
            ->where('leaves_and_absences.status', 'pending')
            ->whereNull('leaves_and_absences.deleted_at');

        $unionQuery = $vacationQuery->union($leaveQuery);

        // We use DB::table to avoid Eloquent global scopes (like SoftDeletes)
        // that would incorrectly prefix columns in the outer query.
        $query = DB::table(DB::raw("({$unionQuery->toSql()}) as combined_requests"))
            ->mergeBindings($unionQuery);

        return $table
            ->query($query)
            ->recordIdentifier('row_key')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Colaborador')
                    ->getStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}"),
                Tables\Columns\TextColumn::make('request_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sick_leave' => 'Baixa Médica',
                        'parental' => 'Licença Parental',
                        'marriage' => 'Casamento',
                        'bereavement' => 'Falecimento',
                        'justified_absence' => 'Falta Justificada',
                        'unjustified' => 'Falta Injustificada',
                        'Férias' => 'Férias',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Férias' => 'success',
                        'sick_leave', 'unjustified' => 'danger',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Início')
                    ->date(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fim')
                    ->date(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $actualRecord = ($record->model_type)::find($record->id);
                        $actualRecord->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Pedido aprovado com sucesso')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->color('danger')
                    ->icon('heroicon-m-x-mark')
                    ->form([
                        Tables\Components\Textarea::make('rejection_reason')
                            ->label('Motivo da Rejeição')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $actualRecord = ($record->model_type)::find($record->id);
                        $actualRecord->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Pedido rejeitado')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
