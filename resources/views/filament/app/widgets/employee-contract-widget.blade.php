<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Informações do Contrato
        </x-slot>

        @php
            $contract = $this->getContract();
        @endphp

        @if($contract)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Tipo de Contrato</p>
                        <p class="font-medium capitalize">{{ str_replace('_', ' ', $contract->type) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Salário Base</p>
                        <p class="font-medium">{{ number_format($contract->salary, 2, ',', '.') }} €</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500 dark:text-gray-400">Designação</p>
                        <p class="font-medium">{{ $contract->designation?->name ?? 'N/A' }}</p>
                    </div>

                    @if(in_array($contract->type, ['temporary', 'fixed_term']))
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Data de Início</p>
                            <p class="font-medium">{{ $contract->start_date?->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Data de Fim</p>
                            <p class="font-medium">{{ $contract->end_date?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                    @endif
                </div>

                <div class="pt-4">
                    <x-filament::button
                        wire:click="download"
                        icon="heroicon-m-arrow-down-tray"
                        color="gray"
                        size="sm"
                        class="w-full"
                    >
                        Descarregar Contrato (PDF)
                    </x-filament::button>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 italic">Nenhum contrato ativo encontrado.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
