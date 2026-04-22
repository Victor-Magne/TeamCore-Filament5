<x-filament-widgets::widget>
    <x-filament::section compact>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            {{-- Lado Esquerdo: Ícone e Texto --}}
            <div class="flex items-center gap-x-3">
                <div class="rounded-lg bg-primary-500/10 p-2">
                    {{--<x-heroicon-m-sparkles class="h-5 w-5 text-primary-500" />--}}
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Atalhos e Pedidos Rápidos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Os pedidos serão analisados pelos Recursos Humanos.
                    </p>
                </div>
            </div>

            {{-- Lado Direito: Botões de Ação --}}
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                {{ $this->requestVacationAction }}
                {{ $this->requestLeaveAction }}
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>