<?php

namespace App\Filament\Widgets;

use App\Services\ContractPdfService;
use Filament\Actions\Action;
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
        return $schema
            ->record($this->getContract())
            ->components([
                // 2. Usar Grid em vez de Section para os itens partilharem a mesma linha horizontal
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
                            ->default('N/A'), // Removido columnSpanFull()

                        // Contratos temporários / a prazo
                        TextEntry::make('start_date')
                            ->label('Início')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->hidden(fn($record) => ! in_array($record?->type, ['temporary', 'fixed_term'])),

                        TextEntry::make('end_date')
                            ->label('Fim Previsto')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->default('Indeterminado')
                            ->hidden(fn($record) => ! in_array($record?->type, ['temporary', 'fixed_term'])),

                        // Contratos sem termo
                        TextEntry::make('start_date')
                            ->label('Data de Admissão')
                            ->size(TextSize::Small)
                            ->date('d/m/Y')
                            ->hidden(fn($record) => in_array($record?->type, ['temporary', 'fixed_term'])),
                    ]),
            ]);
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label('Descarregar (PDF)')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('gray')
            ->size('sm')
            // Em vez de usar ->action(), usamos ->url() para chamar a sua rota diretamente
            ->url(function () {
                $contract = $this->getContract();

                if (! $contract) {
                    return '#';
                }

                // Presumindo que o nome da rota no seu routes/web.php é 'contracts.pdf.single'
                // Ajuste se o nome da rota for ligeiramente diferente
                return route('contracts.pdf.single', $contract);
            })
            ->openUrlInNewTab(); // Abre o PDF numa nova aba sem quebrar a página do Livewire
    }
}
