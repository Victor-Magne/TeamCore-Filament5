<x-filament-widgets::widget>
    <x-filament::section compact>
        
        {{-- Cabeçalho da Secção com o Botão Alinhado à Direita --}}
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-x-1.5">
                    {{-- <x-heroicon-m-document-text class="h-5 w-5 text-gray-400" /> --}}
                    <span class="text-sm font-semibold">Informações do Contrato</span>
                </div>
                
                {{-- Colocamos a ação de download no próprio cabeçalho se houver contrato --}}
                @if($this->getContract())
                    <div>
                        {{ $this->downloadAction }}
                    </div>
                @endif
            </div>
        </x-slot>

        {{-- Corpo da Secção --}}
        @if($this->getContract())
            <div class="pt-2">
                {{ $this->contractInfolist }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-y-1 py-4 text-center">
                <x-heroicon-o-document-minus class="h-7 w-7 text-gray-400" />
                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum contrato ativo encontrado.</p>
            </div>
        @endif
        
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>