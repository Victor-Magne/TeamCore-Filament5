<?php

/**
 * Ficheiro do Observer AbsenceObserver.
 *
 * Este observer monitoriza a criação, actualização e eliminação de registos de ausência (Absence).
 * As ausências são deduções directas no banco de horas. Qualquer alteração nestes
 * registos obriga à sincronização incremental do saldo do funcionário.
 */

namespace App\Observers;

use App\Models\Absence;
use App\Services\Hour\HourBankService;

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
        $this->hourBankService->syncAbsence($absence);
    }

    /**
     * Manipula o evento "updated" do Modelo Absence.
     *
     * @param  Absence  $absence  O registo de ausência actualizado.
     */
    public function updated(Absence $absence): void
    {
        // O syncAbsence utiliza updateOrCreate no HourBankMovement baseado no ID da Absence,
        // o que garante a actualização correcta do montante e descrição.
        $this->hourBankService->syncAbsence($absence);
    }

    /**
     * Manipula o evento "deleted" do Modelo Absence.
     *
     * @param  Absence  $absence  O registo de ausência eliminado.
     */
    public function deleted(Absence $absence): void
    {
        // Remover o movimento associado e devolver as horas ao saldo
        $this->hourBankService->removeMovement(Absence::class, $absence->id);
    }
}
