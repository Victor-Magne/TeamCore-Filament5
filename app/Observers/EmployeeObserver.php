<?php

/**
 * Ficheiro do Observer EmployeeObserver.
 *
 * Este observer automatiza o processo de "onboarding" de um novo funcionário
 * delegando a lógica para o serviço EmployeeOnboardingService.
 */

namespace App\Observers;

use App\Models\Employee;
use App\Services\Employee\EmployeeOnboardingService;

class EmployeeObserver
{
    /**
     * Serviço de onboarding de funcionários.
     */
    protected EmployeeOnboardingService $onboardingService;

    /**
     * Construtor com injecção de dependência.
     */
    public function __construct(EmployeeOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Manipula o evento "created" do Modelo Employee.
     *
     * @param  Employee  $employee  O funcionário que acabou de ser criado.
     */
    public function created(Employee $employee): void
    {
        $this->onboardingService->handle($employee);
    }
}
