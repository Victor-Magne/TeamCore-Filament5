<?php

namespace App\Services\Vacation;

use App\Models\Employee;
use App\Models\Vacation;
use Illuminate\Support\Facades\DB;

class VacationBalanceService
{
    /**
     * Deduz os dias do saldo quando um pedido é aprovado.
     * Usa lockForUpdate para evitar race conditions em aprovações simultâneas.
     */
    public function deductOnApproval(Vacation $vacation): void
    {
        if ($vacation->days_taken <= 0) {
            return;
        }

        DB::transaction(function () use ($vacation) {
            $employee = Employee::lockForUpdate()->find($vacation->employee_id);

            if (! $employee) {
                return;
            }

            if ($employee->vacation_balance < $vacation->days_taken) {
                throw new \RuntimeException("Saldo insuficiente: disponível {$employee->vacation_balance} dia(s), necessário {$vacation->days_taken}.");
            }

            $employee->decrement('vacation_balance', $vacation->days_taken);
        });
    }

    /**
     * Devolve os dias ao saldo quando um pedido aprovado é rejeitado/cancelado.
     */
    public function restoreOnRevocation(Vacation $vacation): void
    {
        $days = $vacation->getOriginal('days_taken') ?? $vacation->days_taken;

        if ($days <= 0) {
            return;
        }

        DB::transaction(function () use ($vacation, $days) {
            $employee = Employee::lockForUpdate()->find($vacation->employee_id);

            if ($employee) {
                $employee->increment('vacation_balance', $days);
            }
        });
    }

    /**
     * Ajusta o saldo quando a duração de um pedido já aprovado é alterada.
     */
    public function adjustOnDaysChange(Vacation $vacation): void
    {
        $diff = $vacation->days_taken - $vacation->getOriginal('days_taken');

        if ($diff === 0) {
            return;
        }

        DB::transaction(function () use ($vacation, $diff) {
            $employee = Employee::lockForUpdate()->find($vacation->employee_id);

            if (! $employee) {
                return;
            }

            if ($diff > 0) {
                if ($employee->vacation_balance < $diff) {
                    throw new \RuntimeException("Saldo insuficiente para esta alteração: disponível {$employee->vacation_balance} dia(s), necessário {$diff}.");
                }

                $employee->decrement('vacation_balance', $diff);
            } else {
                $employee->increment('vacation_balance', abs($diff));
            }
        });
    }

    /**
     * Devolve os dias ao saldo quando um pedido aprovado é eliminado.
     */
    public function restoreOnDelete(Vacation $vacation): void
    {
        if ($vacation->status !== 'approved' || $vacation->days_taken <= 0) {
            return;
        }

        DB::transaction(function () use ($vacation) {
            $employee = Employee::lockForUpdate()->find($vacation->employee_id);

            if ($employee) {
                $employee->increment('vacation_balance', $vacation->days_taken);
            }
        });
    }
}
