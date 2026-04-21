<x-filament-widgets::widget>
    <x-filament::section class="h-full">
        @php
            $employee = $this->getEmployee();
            $initials = $employee ? collect(explode(' ', $employee->full_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') : '??';
        @endphp

        @if($employee)
            <div class="flex flex-col h-full space-y-6">
                <!-- Avatar and Name Section -->
                <div class="flex items-center gap-x-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-500 text-white text-xl font-bold dark:bg-primary-600">
                        {{ strtoupper($initials) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ $employee->full_name }}
                        </h2>
                        <p class="text-sm text-primary-600 font-medium dark:text-primary-400">
                            {{ $employee->designation?->name ?? 'Sem Cargo Definido' }}
                        </p>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-white/5" />

                <!-- Details Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-envelope class="h-4 w-4" />
                            <span>Email Corporativo</span>
                        </div>
                        <p class="text-sm font-semibold truncate">{{ $employee->email }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-building-office class="h-4 w-4" />
                            <span>Unidade / Departamento</span>
                        </div>
                        <p class="text-sm font-semibold">{{ $employee->unit?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-phone class="h-4 w-4" />
                            <span>Contacto</span>
                        </div>
                        <p class="text-sm font-semibold">{{ $employee->phone_number ?? 'Não registado' }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-calendar class="h-4 w-4" />
                            <span>Data de Admissão</span>
                        </div>
                        <p class="text-sm font-semibold">{{ $employee->date_hired?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($employee->address)
                    <div class="space-y-1 pt-2">
                        <div class="flex items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-map-pin class="h-4 w-4" />
                            <span>Localização</span>
                        </div>
                        <p class="text-sm font-semibold text-pretty">{{ $employee->address }}, {{ $employee->zip_code }} {{ $employee->city?->name }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <div class="rounded-full bg-danger-50 p-3 dark:bg-danger-500/10">
                    <x-heroicon-o-user-minus class="h-8 w-8 text-danger-600 dark:text-danger-400" />
                </div>
                <p class="mt-4 text-sm font-medium text-gray-950 dark:text-white">Perfil não encontrado</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nenhum perfil de funcionário associado ao seu utilizador.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
