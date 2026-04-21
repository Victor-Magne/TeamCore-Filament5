<x-filament-widgets::widget>
    <x-filament::section class="h-full">
        <x-slot name="heading">
            <div class="flex items-center gap-x-2">
                <x-heroicon-m-document-text class="h-5 w-5 text-gray-400" />
                <span>Informações do Contrato</span>
            </div>
        </x-slot>

        @php
            $contract = $this->getContract();
        @endphp

        @if($contract)
            <div class="flex flex-col h-full space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</p>
                        <x-filament::badge color="info" size="sm" class="w-fit">
                            {{ str_replace('_', ' ', $contract->type) }}
                        </x-filament::badge>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Remuneração Base</p>
                        <p class="text-sm font-bold text-gray-950 dark:text-white">
                            {{ number_format($contract->salary, 2, ',', '.') }} €
                        </p>
                    </div>

                    <div class="col-span-2 space-y-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Vínculo</p>
                        <p class="text-sm font-semibold">{{ $contract->designation?->name ?? 'N/A' }}</p>
                    </div>

                    @if(in_array($contract->type, ['temporary', 'fixed_term']))
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Início</p>
                            <p class="text-sm font-semibold">{{ $contract->start_date?->format('d/m/Y') }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Fim Previsto</p>
                            <p class="text-sm font-semibold">{{ $contract->end_date?->format('d/m/Y') ?? 'Indeterminado' }}</p>
                        </div>
                    @else
                        <div class="col-span-2 space-y-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data de Admissão</p>
                            <p class="text-sm font-semibold">{{ $contract->start_date?->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>

                <div class="pt-4 mt-auto">
                    {{ $this->downloadAction }}
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <x-heroicon-o-document-minus class="h-8 w-8 text-gray-400" />
                <p class="mt-2 text-sm text-gray-500 italic">Nenhum contrato ativo encontrado.</p>
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
