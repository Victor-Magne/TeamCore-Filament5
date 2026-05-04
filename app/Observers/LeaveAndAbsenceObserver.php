<?php

/**
 * Ficheiro do Observer LeaveAndAbsenceObserver.
 *
 * Este observer gere o impacto das licenças e ausências justificadas no sistema.
 * Quando uma licença é aprovada, o sistema limpa automaticamente faltas injustificadas
 * ou atrasos que tenham sido registados pelo sistema de ponto para esse período.
 * Se a licença não for paga, converte o período em deduções formais.
 */

namespace App\Observers;

use App\Models\LeaveAndAbsence;
use App\Services\Hour\DeductHourBankService;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class LeaveAndAbsenceObserver
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
     * Manipula o evento "saved" (created/updated) do Modelo LeaveAndAbsence.
     *
     * @param  LeaveAndAbsence  $leaveAndAbsence  O registo de licença.
     */
    public function saved(LeaveAndAbsence $leaveAndAbsence): void
    {
        // Só processamos impacto no banco de horas se a licença estiver aprovada
        if ($leaveAndAbsence->status === 'approved') {
            $startDate = Carbon::parse($leaveAndAbsence->start_date);
            $endDate = Carbon::parse($leaveAndAbsence->end_date);

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                // Apenas dias úteis são contabilizados para faltas/licenças
                if ($currentDate->isWeekday()) {
                    // Se for licença não paga (ex: faltas justificadas sem vencimento), deve descontar horas
                    if (! $leaveAndAbsence->is_paid) {
                        $this->deductService->registerFullAbsence(
                            $leaveAndAbsence->employee_id,
                            $currentDate->copy(),
                            'Licença aprovada (Não paga): '.($leaveAndAbsence->reason ?? $leaveAndAbsence->type)
                        );
                    } else {
                        // Se for paga (ex: baixa médica), removemos qualquer falta automática
                        // gerada pelo sistema por falta de picagem de ponto.
                        $this->deductService->removeAbsenceForDate($leaveAndAbsence->employee_id, $currentDate->toDateString());
                    }
                }
                $currentDate->addDay();
            }

            // Recalcular banco de horas para os meses afectados pela licença
            $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $startDate->format('Y-m'));
            if ($startDate->format('Y-m') !== $endDate->format('Y-m')) {
                $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $endDate->format('Y-m'));
            }
        }
    }

    /**
     * Manipula o evento "deleted" do Modelo LeaveAndAbsence.
     *
     * @param  LeaveAndAbsence  $leaveAndAbsence  O registo eliminado.
     */
    public function deleted(LeaveAndAbsence $leaveAndAbsence): void
    {
        // Se uma licença for apagada, precisamos de recalcular para restaurar faltas se necessário
        $startDate = Carbon::parse($leaveAndAbsence->start_date);
        $this->hourBankService->recalculate($leaveAndAbsence->employee_id, $startDate->format('Y-m'));
    }
}
