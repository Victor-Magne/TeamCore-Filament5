<?php

/**
 * Ficheiro do Observer AttendanceLogObserver.
 *
 * Este observer monitoriza as alterações nos registos de presença (AttendanceLog).
 * Quando um registo é criado, actualizado ou eliminado, despoleta o recálculo
 * automático do saldo do Banco de Horas e o processamento de ausências/atrasos.
 */

namespace App\Observers;

use App\Models\AttendanceLog;
use App\Services\Hour\DeductHourBankService;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class AttendanceLogObserver
{
    /** Serviço de gestão do banco de horas. */
    protected HourBankService $hourBankService;

    /** Serviço de deduções de assiduidade. */
    protected DeductHourBankService $deductService;

    /**
     * Construtor com injecção de dependências.
     */
    public function __construct(HourBankService $hourBankService, DeductHourBankService $deductService)
    {
        $this->hourBankService = $hourBankService;
        $this->deductService = $deductService;
    }

    /**
     * Manipula o evento "created" do Modelo AttendanceLog.
     *
     * @param  AttendanceLog  $attendanceLog  O registo de presença criado.
     */
    public function created(AttendanceLog $attendanceLog): void
    {
        // Processa atrasos e faltas baseados no ponto
        $this->deductService->processAttendance($attendanceLog);

        // Recalcular banco de horas para este mês
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }

    /**
     * Manipula o evento "updated" do Modelo AttendanceLog.
     *
     * @param  AttendanceLog  $attendanceLog  O registo de presença actualizado.
     */
    public function updated(AttendanceLog $attendanceLog): void
    {
        // Só reprocessa se campos críticos de tempo foram alterados
        if ($attendanceLog->isDirty(['time_in', 'time_out', 'total_minutes'])) {

            // Se mudou o mês da entrada, recalcular o mês original para corrigir saldos
            if ($attendanceLog->isDirty('time_in')) {
                $originalTimeIn = $attendanceLog->getOriginal('time_in');
                if ($originalTimeIn) {
                    $this->hourBankService->recalculate(
                        $attendanceLog->employee_id,
                        Carbon::parse($originalTimeIn)->format('Y-m')
                    );
                }
            }

            // Reprocessar as ausências/atrasos para a data do registo
            $this->deductService->processAttendance($attendanceLog);

            // Recalcular o mês actual do registo
            $this->hourBankService->recalculate(
                $attendanceLog->employee_id,
                $attendanceLog->time_in->format('Y-m')
            );
        }
    }

    /**
     * Manipula o evento "deleted" do Modelo AttendanceLog.
     *
     * @param  AttendanceLog  $attendanceLog  O registo de presença eliminado.
     */
    public function deleted(AttendanceLog $attendanceLog): void
    {
        // Remover Absence automática se o ponto for apagado, para não penalizar indevidamente
        $this->deductService->removeAbsenceForDate($attendanceLog->employee_id, $attendanceLog->time_in->toDateString());

        // Recalcula o mês após a remoção do registo
        $this->hourBankService->recalculate(
            $attendanceLog->employee_id,
            $attendanceLog->time_in->format('Y-m')
        );
    }
}
