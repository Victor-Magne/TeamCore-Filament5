<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <form wire:submit="changePassword" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-start gap-3">
                <x-filament::button
                    type="submit"
                >
                    {{ __('Alterar Password') }}
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
