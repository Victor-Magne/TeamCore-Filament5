<x-filament-widgets::widget>
    <x-filament::section class="h-full">
        <x-slot name="heading">
            <div class="flex items-center gap-x-2">
                <x-heroicon-m-sparkles class="h-5 w-5 text-primary-500" />
                <span>Atalhos e Pedidos Rápidos</span>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 gap-4">
            <div class="group">
                {{ ($this->requestVacationAction)(['class' => 'w-full justify-start py-4 ring-1 ring-gray-200 dark:ring-white/10 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition-colors']) }}
            </div>

            <div class="group">
                {{ ($this->requestLeaveAction)(['class' => 'w-full justify-start py-4 ring-1 ring-gray-200 dark:ring-white/10 hover:bg-secondary-50 dark:hover:bg-secondary-500/10 transition-colors']) }}
            </div>
        </div>

        <div class="mt-6 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-dashed border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed text-center">
                Utilize os botões acima para submeter novos pedidos. Todos os pedidos serão analisados pelos Recursos Humanos.
            </p>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
