<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center space-y-4">
        <div class="text-2xl font-bold">
            {{ now()->format('H:i:s') }}
        </div>
        <div class="text-gray-500">
            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
        </div>

        <div class="mt-8">
            {{ $this->checkInAction }}
        </div>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
