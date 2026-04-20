<?php

namespace App\Filament\App\Widgets;

use App\Services\ContractPdfService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeContractWidget extends Widget
{
    protected string $view = 'filament.app.widgets.employee-contract-widget';

    protected int|string|array $columnSpan = 1;

    public function getContract()
    {
        return Auth::user()->employee?->contracts()
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    public function download()
    {
        $contract = $this->getContract();

        if (! $contract) {
            return;
        }

        return app(ContractPdfService::class)->downloadSingleContractPdf($contract);
    }
}
