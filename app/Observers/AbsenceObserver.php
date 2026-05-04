<?php

/**
 * Ficheiro do Observer AbsenceObserver.
 *
 * Este observer monitoriza a criação, actualização e eliminação de registos de ausência (Absence).
 * As ausências são deduções directas no banco de horas. Qualquer alteração nestes
 * registos obriga ao recálculo imediato do saldo do funcionário para o mês correspondente.
 */

namespace App\Observers;

use App\Models\Absence;
use App\Services\Hour\HourBankService;
use Carbon\Carbon;

class AbsenceObserver
{
    /** Serviço de gestão do banco de horas. */
    protected HourBankService $hourBankService;

    /**
     * Construtor com injecção de dependência.
     */
    public function __construct(HourBankService $hourBankService)
    {
        $this->hourBankService = $hourBankService;
    }

    /**
     * Manipula o evento "created" do Modelo Absence.
     *
     * @param  Absence  $absence  O registo de ausência criado.
     */
    public function created(Absence $absence): void
    {
        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }

    /**
     * Manipula o evento "updated" do Modelo Absence.
     *
     * @param  Absence  $absence  O registo de ausência actualizado.
     */
    public function updated(Absence $absence): void
    {
        // Se a data da ausência mudou, precisamos de recalcular ambos os meses (antigo e novo)
        if ($absence->isDirty('absence_date')) {
            $originalDate = $absence->getOriginal('absence_date');
            if ($originalDate) {
                $this->hourBankService->recalculate(
                    $absence->employee_id,
                    Carbon::parse($originalDate)->format('Y-m')
                );
            }
        }

        // Recalcula o mês actual do registo
        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }

    /**
     * Manipula o evento "deleted" do Modelo Absence.
     *
     * @param  Absence  $absence  O registo de ausência eliminado.
     */
    public function deleted(Absence $absence): void
    {
        // Recalcula para devolver as horas ao saldo do banco de horas
        $this->hourBankService->recalculate(
            $absence->employee_id,
            $absence->absence_date->format('Y-m')
        );
    }
}
