@php
    $hasChildren = $unit->children->isNotEmpty();
    $isOpen = $depth === 0;

    $badgeClasses = match ($unit->type) {
        'direction'  => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
        'department' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
        'section'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        default      => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };

    $typeLabel = match ($unit->type) {
        'direction'  => 'Direção',
        'department' => 'Departamento',
        'section'    => 'Secção',
        default      => ucfirst($unit->type ?? 'Outro'),
    };

    $rowBg = $depth === 0
        ? 'bg-gray-50 dark:bg-gray-800/60'
        : 'bg-white dark:bg-gray-900';
@endphp

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">

    {{-- Row --}}
    <div class="group flex items-center gap-3 px-4 py-3 {{ $rowBg }} hover:bg-gray-100/80 dark:hover:bg-white/5 transition-colors duration-150">

        {{-- Toggle / leaf indicator --}}
        @if ($hasChildren)
            <button
                @click="open = !open"
                :aria-expanded="open"
                class="shrink-0 w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
            >
                {{-- Chevron right, 16px --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                    class="w-3.5 h-3.5 transition-transform duration-200"
                    :class="{ 'rotate-90': open }"
                >
                    <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L9.19 8 6.22 5.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        @else
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
            </div>
        @endif

        {{-- Type badge --}}
        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeClasses }}">
            {{ $typeLabel }}
        </span>

        {{-- Name --}}
        <span class="flex-1 min-w-0 font-semibold text-sm text-gray-900 dark:text-white truncate">
            {{ $unit->name }}
        </span>

        {{-- Managers --}}
        <div class="hidden sm:flex items-center gap-1 shrink-0">
            @forelse ($unit->managers as $manager)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    {{ $manager->first_name }}
                </span>
            @empty
                <span class="text-xs text-gray-400 italic">Sem gestores</span>
            @endforelse
        </div>

        {{-- Staff badge --}}
        <span class="shrink-0 inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
            {{ $unit->employees_count }}
        </span>

        {{-- Actions (visible on row hover) --}}
        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
            <a
                href="{{ \App\Filament\Resources\Units\UnitResource::getUrl('view', ['record' => $unit]) }}"
                class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                {{-- Eye, 16px --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
                    <path fill-rule="evenodd" d="M1.38 8a6.75 6.75 0 0 1 13.24 0A6.75 6.75 0 0 1 1.38 8ZM8 5a3 3 0 1 0 0 6A3 3 0 0 0 8 5Z" clip-rule="evenodd" />
                </svg>
                <span class="hidden lg:inline">Visualizar</span>
            </a>
            <a
                href="{{ \App\Filament\Resources\Units\UnitResource::getUrl('edit', ['record' => $unit]) }}"
                class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                {{-- Pencil square, 16px --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.262a1.75 1.75 0 0 0 0-2.474Z" />
                    <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9a.75.75 0 0 1 1.5 0v2.25A2.75 2.75 0 0 1 11.25 14h-6.5A2.75 2.75 0 0 1 2 11.25v-6.5A2.75 2.75 0 0 1 4.75 2H7a.75.75 0 0 1 0 1.5H4.75Z" />
                </svg>
                <span class="hidden lg:inline">Editar</span>
            </a>
        </div>
    </div>

    {{-- Children --}}
    @if ($hasChildren)
        <div
            x-show="open"
            x-collapse
            class="border-t border-gray-100 dark:border-gray-800"
        >
            @foreach ($unit->children as $child)
                <div class="border-b border-gray-100 dark:border-gray-800 last:border-b-0 ml-6 border-l-2 border-l-gray-100 dark:border-l-gray-800">
                    @include('filament.resources.units.pages.partials.unit-node', [
                        'unit' => $child,
                        'depth' => $depth + 1,
                    ])
                </div>
            @endforeach
        </div>
    @endif
</div>
