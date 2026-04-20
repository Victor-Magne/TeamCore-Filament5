<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Atalhos e Pedidos
        </x-slot>

        <div class="flex flex-col gap-y-3">
            {{ $this->requestVacationAction }}

            {{ $this->requestLeaveAction }}
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
