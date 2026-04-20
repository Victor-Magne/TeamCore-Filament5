<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Informações Pessoais
        </x-slot>

        @php
            $employee = $this->getEmployee();
        @endphp

        @if($employee)
            <div class="space-y-4">
                <div class="flex items-center gap-x-3">
                    <div class="flex-1 text-sm">
                        <p class="text-gray-500 dark:text-gray-400">Nome</p>
                        <p class="font-medium">{{ $employee->full_name }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-x-3">
                    <div class="flex-1 text-sm">
                        <p class="text-gray-500 dark:text-gray-400">Cargo</p>
                        <p class="font-medium">{{ $employee->designation?->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-x-3">
                    <div class="flex-1 text-sm">
                        <p class="text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium">{{ $employee->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-x-3">
                    <div class="flex-1 text-sm">
                        <p class="text-gray-500 dark:text-gray-400">Unidade</p>
                        <p class="font-medium">{{ $employee->unit?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 italic">Nenhum perfil de funcionário associado.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
