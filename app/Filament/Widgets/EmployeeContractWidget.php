<?php

namespace App\Filament\Widgets;

use App\Services\ContractPdfService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeContractWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.widgets.employee-contract-widget';

    protected int|string|array $columnSpan = 1;

    public function getContract()
    {
        return Auth::user()->employee?->contracts()
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label('Descarregar Contrato (PDF)')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('gray')
            ->action(function () {
                $contract = $this->getContract();

                if (! $contract) {
                    return;
                }

                return app(ContractPdfService::class)->downloadSingleContractPdf($contract);
            });
    }
}
