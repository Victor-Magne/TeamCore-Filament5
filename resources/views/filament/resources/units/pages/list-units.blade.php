<x-filament-panels::page>
    <div class="space-y-3">
        @forelse($this->getRoots() as $unit)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900">
                @include('filament.resources.units.pages.partials.unit-node', [
                    'unit' => $unit,
                    'depth' => 0,
                ])
            </div>
        @empty
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center bg-white dark:bg-gray-900">
                {{-- Building Office outline, 24px --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Sem unidades registadas.</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Crie a primeira unidade organizacional.</p>
            </div>
        @endforelse
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
