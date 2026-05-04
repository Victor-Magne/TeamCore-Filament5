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

            // O impacto no banco de horas é agora gerido automaticamente pelos
            // observers de Absence e AttendanceLog que são disparados pelas chamadas acima.
        }
    }

    /**
     * Manipula o evento "deleted" do Modelo LeaveAndAbsence.
     *
     * @param  LeaveAndAbsence  $leaveAndAbsence  O registo eliminado.
     */
    public function deleted(LeaveAndAbsence $leaveAndAbsence): void
    {
        // Se uma licença for apagada, as Absences relacionadas continuam a existir
        // a menos que o utilizador as apague manualmente. O saldo incremental
        // manter-se-á consistente.
    }
}
