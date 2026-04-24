<?php

namespace App\Filament\Widgets;

use App\Services\ContractPdfService;
use Filament\Actions\Action;
use App\Models\Employee;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid; // Alterado de Section para Grid
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

use Filament\Support\Enums\TextSize;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeContractWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.employee-contract-widget';

    // 1. Expandido para ocupar a largura total
    protected int|string|array $columnSpan = 2;

    public function getContract()
    {
        return Auth::user()->employee?->contracts()
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    public function contractInfolist(Schema $schema): Schema
    {
        $contract = $this->getContract(); // ✅ atribuir à variável
        $employee = $this->getEmployee();

        return $schema
            ->record($contract)
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 4,
                    'lg' => 5,
                ])
                    ->schema([
                        TextEntry::make('type')
                            ->label('Tipo')
                            ->size(TextSize::Small)
                            ->badge()
                            ->formatStateUsing(fn($state) => str_replace('_', ' ', ucfirst($state))),
                        TextEntry::make('salary')
                            ->label('Remuneração Base')
                            ->size(TextSize::Small)
                            ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' €'),
                        TextEntry::make('designation.name') 
                            ->label('Vínculo')
                            ->size(TextSize::Small)
                            ->default('N/A'),
                        TextEntry::make('start_date')
                            ->label('Início')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->hidden(fn($record) => ! in_array($record?->type, ['temporary', 'fixed_term'])),
                        TextEntry::make('end_date')
                            ->label('Fim Previsto')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->placeholder('Indeterminado') // ✅ placeholder em vez de default
                            ->hidden(fn($record) => ! in_array($record?->type, ['temporary', 'fixed_term'])),
                        TextEntry::make('start_date')
                            ->label('Data de Admissão')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->hidden(fn($record) => in_array($record?->type, ['temporary', 'fixed_term'])),
                    ])
                    ->visible(fn() => $contract !== null)// ✅
                    ->visible(fn() => $employee !== null), // ✅
            ]);
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label('Descarregar (PDF)')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('gray')
            ->size('sm')
            ->visible(fn() => $this->getContract() !== null) // ✅
            ->url(function () {
                $contract = $this->getContract();
                if (! $contract) return '#';
                return route('contracts.pdf.single', $contract);
            })
            ->openUrlInNewTab();
    }
}
