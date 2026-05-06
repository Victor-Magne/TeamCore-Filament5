<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamPendingRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pedidos Pendentes da Equipa';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $meuEmployee = $user?->employee;

        if (! $meuEmployee) {
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

        $leaveQuery = LeaveAndAbsence::query()
            ->select([
                'id',
                'employee_id',
                'start_date',
                'end_date',
                'status',
                DB::raw('type as request_type'),
                DB::raw("'App\\\\Models\\\\LeaveAndAbsence' as model_type"),
                DB::raw("CONCAT('App\\\\\\\\Models\\\\\\\\LeaveAndAbsence', ':', id) as row_key"),
            ])
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'pending');

        // We use one of the models as the base for the query builder but override the whole query with a union
        $vacationQuery = Vacation::query()
            ->select([
                'id',
                'employee_id',
                'start_date',
                'end_date',
                'status',
                DB::raw("'Férias' as request_type"),
                DB::raw("'App\\\\Models\\\\Vacation' as model_type"),
                DB::raw("CONCAT('App\\\\\\\\Models\\\\\\\\Vacation', ':', id) as row_key"),
            ])
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'pending');

        // Wrap the union in a subquery to allow pagination and sorting
        $query = Vacation::query()
            ->withoutGlobalScopes()
            ->select('*')
            ->fromSub($vacationQuery->union($leaveQuery), 'combined_requests');

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
                    ->action(function (Model $record) {
                        $actualRecord = $this->resolveActionableRecord($record);
                        if (! $actualRecord) {
                            Notification::make()
                                ->title('Pedido inválido ou não autorizado')
                                ->danger()
                                ->send();

                            return;
                        }

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
                        Textarea::make('rejection_reason')
                            ->label('Motivo da Rejeição')
                            ->required(),
                    ])
                    ->action(function (Model $record, array $data) {
                        $actualRecord = $this->resolveActionableRecord($record);
                        if (! $actualRecord) {
                            Notification::make()
                                ->title('Pedido inválido ou não autorizado')
                                ->danger()
                                ->send();

                            return;
                        }

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

    public function getTableRecordKey(Model|array $record): string
    {
        if (is_array($record)) {
            if (filled($record['row_key'] ?? null)) {
                return (string) $record['row_key'];
            }

            return parent::getTableRecordKey($record);
        }

        if (filled($record->row_key ?? null)) {
            return (string) $record->row_key;
        }

        return parent::getTableRecordKey($record);
    }

    private function resolveActionableRecord(Model $record): ?Model
    {
        $employee = Auth::user()?->employee;
        if (! $employee) {
            return null;
        }

        $modelClass = (string) ($record->model_type ?? '');
        if (! in_array($modelClass, [Vacation::class, LeaveAndAbsence::class], true)) {
            return null;
        }

        $managedEmployeeIds = $employee->getAllSubordinateEmployeeIds();
        if (! in_array((int) $record->employee_id, $managedEmployeeIds, true)) {
            return null;
        }

        return $modelClass::query()
            ->whereKey($record->id)
            ->where('employee_id', $record->employee_id)
            ->where('status', 'pending')
            ->first();
    }
}
