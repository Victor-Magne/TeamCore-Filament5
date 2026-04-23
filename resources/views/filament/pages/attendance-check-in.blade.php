<x-filament-panels::page>
    <div class="space-y-6" x-data="{
        time: '--:--:--',
        date: '',
        workedTime: '0h 0m',
        serverOffset: 0,
        in: @entangle('timeIn'),
        l_start: @entangle('lunchStart'),
        l_end: @entangle('lunchEnd'),
        out: @entangle('timeOut'),
        serverTs: {{ $serverTimestamp }},
        init() {
            let clientTs = Math.floor(Date.now() / 1000);
            this.serverOffset = this.serverTs - clientTs;
            this.update();
            setInterval(() => this.update(), 1000);
        },
        update() {
            let nowTs = Math.floor(Date.now() / 1000) + this.serverOffset;
            let adjustedNow = new Date(nowTs * 1000);
            this.time = adjustedNow.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = adjustedNow.toLocaleDateString('pt-PT', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            if (!this.in) { this.workedTime = '0h 0m'; return; }
            let endTs = this.out ? this.out : nowTs;
            let totalSeconds = endTs - this.in;
            if (this.l_start) {
                let lEnd = this.l_end ? this.l_end : (this.out ? this.out : nowTs);
                totalSeconds -= (lEnd - this.l_start);
            }
            totalSeconds = Math.max(0, totalSeconds);
            this.workedTime = Math.floor(totalSeconds / 3600) + 'h ' + Math.floor((totalSeconds % 3600) / 60) + 'm';
        }
    }">

       {{-- Cabeçalho Minimalista --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Olá, {{ explode(' ', auth()->user()->name)[0] }}
                </h1>
                <p class="text-sm text-gray-500">Controlo de ponto diário</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-mono font-bold text-gray-900 dark:text-white" x-text="time"></p>
                <p class="text-xs text-gray-400 uppercase tracking-wider" x-text="date"></p>
            </div>
        </div>

        {{-- Ação Principal (Foco no botão) --}}
        <div class="border-t border-b border-gray-100 dark:border-gray-800 py-8 flex flex-col items-center">
            <h2 class="text-sm font-medium text-gray-600 mb-4">{{ $this->getCheckInLabel() }}</h2>
            <div class="w-full max-w-xs">
                {{ $this->checkInAction }}
            </div>
        </div>

        {{-- Resumo em linha (substitui os 4 blocos complexos) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach(['Entrada' => 'timeIn', 'Almoço' => 'lunchStart', 'Regresso' => 'lunchEnd', 'Saída' => 'timeOut'] as $label => $prop)
                <div class="p-4 border border-gray-100 dark:border-gray-800 rounded text-center">
                    <p class="text-[10px] uppercase text-gray-400 font-bold">{{ $label }}</p>
                    <p class="mt-1 font-mono text-lg" x-text="$wire.{{ $prop }} ? new Date($wire.{{ $prop }} * 1000).toLocaleTimeString('pt-PT', {hour:'2-digit',minute:'2-digit'}) : '--:--'"></p>
                </div>
            @endforeach
        </div>

        {{-- Contador de horas (Discreto) --}}
        <div class="text-center pt-4">
            <p class="text-xs text-gray-400 mb-1">Tempo total trabalhado</p>
            <p class="text-4xl font-light text-primary-600" x-text="workedTime"></p>
        </div>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
