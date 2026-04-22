<x-filament-panels::page>
    <div class="w-full max-w-6xl mx-auto space-y-6" x-data="{
        time: '--:--:--',
        date: '',
        workedTime: '0h 0m',
        serverOffset: 0,
    
        // Ligação direta às propriedades do PHP (Reatividade instantânea)
        in: @entangle('timeIn'),
        l_start: @entangle('lunchStart'),
        l_end: @entangle('lunchEnd'),
        out: @entangle('timeOut'),
        serverTs: {{ $serverTimestamp }},
    
        init() {
            // Calcula a diferença entre o relógio do PC e do Servidor
            let clientTs = Math.floor(Date.now() / 1000);
            this.serverOffset = this.serverTs - clientTs;
    
            this.update();
            setInterval(() => this.update(), 1000);
        },
    
        update() {
            // Hora atual ajustada pelo servidor
            let now = new Date();
            let nowTs = Math.floor(now.getTime() / 1000) + this.serverOffset;
            let adjustedNow = new Date(nowTs * 1000);
    
            // Atualiza Relógio Digital (Segundos visíveis aqui)
            this.time = adjustedNow.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = adjustedNow.toLocaleDateString('pt-PT', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
            // Cálculo das Horas Trabalhadas
            if (!this.in) {
                this.workedTime = '0h 0m';
                return;
            }
    
            let endTs = this.out ? this.out : nowTs;
            let totalSeconds = endTs - this.in;
    
            if (this.l_start) {
                let lEnd = this.l_end ? this.l_end : (this.out ? this.out : nowTs);
                totalSeconds -= (lEnd - this.l_start);
            }
    
            totalSeconds = Math.max(0, totalSeconds);
            let h = Math.floor(totalSeconds / 3600);
            let m = Math.floor((totalSeconds % 3600) / 60);
    
            this.workedTime = h + 'h ' + m + 'm';
        }
    }">

        {{-- Cabeçalho --}}
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-6 lg:p-8 gap-6">
            <div class="flex items-center gap-6">
                <div
                    class="hidden sm:flex h-14 w-14 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 ring-1 ring-primary-500/20 shrink-0">
                    <x-heroicon-m-user class="shrink-0" style="width: 32px; height: 32px;" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Olá, {{ explode(' ', auth()->user()->name)[0] }}! 👋
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sincronizado com o horário do servidor.</p>
                </div>
            </div>

            <div class="text-left md:text-right">
                <h2 class="text-4xl lg:text-5xl font-black tracking-tighter text-primary-600 dark:text-primary-400 font-mono leading-none" x-text="time"></h2>
                <p class="text-sm font-medium text-gray-500 capitalize dark:text-gray-400 mt-2" x-text="date"></p>
            </div>
        </div>

        {{-- Dashboard Central --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-8 flex flex-col items-center justify-center min-h-[300px] relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-primary-50/50 to-transparent dark:from-primary-950/10 pointer-events-none">
                </div>
                <div class="relative z-10 flex flex-col items-center gap-8">
                    {{ $this->checkInAction }}
                </div>
            </div>

            {{-- Horas Trabalhadas --}}
            <div
                class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-8 flex flex-col items-center justify-center text-center gap-4 border-b-4 border-primary-500">
                <div
                    class="h-16 w-16 flex items-center justify-center bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-2xl">
                    <x-heroicon-o-briefcase style="width: 36px; height: 36px;" />
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Trabalhado Hoje</p>
                    <h3 class="text-4xl font-bold text-gray-900 dark:text-white mt-2 font-mono" x-text="workedTime">
                    </h3>
                </div>
            </div>
        </div>

        {{-- Resumo Horizontal --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $steps = [
                    [
                        'l' => 'Entrada',
                        'v' => 'timeIn',
                        'i' => 'heroicon-m-arrow-right-end-on-rectangle',
                        'c' => 'success',
                    ],
                    ['l' => 'Almoço', 'v' => 'lunchStart', 'i' => 'heroicon-m-cake', 'c' => 'warning'],
                    ['l' => 'Regresso', 'v' => 'lunchEnd', 'i' => 'heroicon-m-briefcase', 'c' => 'info'],
                    [
                        'l' => 'Saída',
                        'v' => 'timeOut',
                        'i' => 'heroicon-m-arrow-left-start-on-rectangle',
                        'c' => 'danger',
                    ],
                ];
            @endphp
            @foreach ($steps as $step)
                <div
                    class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-6 flex flex-col items-center gap-3">
                    <div :class="$wire.{{ $step['v'] }} ?
                        'bg-{{ $step['c'] }}-50 text-{{ $step['c'] }}-600 ring-1 ring-{{ $step['c'] }}-500/20' :
                        'bg-gray-50 text-gray-300 dark:bg-gray-800/50'"
                        class="flex h-14 w-14 items-center justify-center rounded-xl transition-colors duration-500">
                        <x-dynamic-component :component="$step['i']" style="width: 32px; height: 32px;" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ $step['l'] }}</span>
                    <span class="text-2xl font-mono font-bold"
                        x-text="$wire.{{ $step['v'] }} ? new Date($wire.{{ $step['v'] }} * 1000).toLocaleTimeString('pt-PT', {hour: '2-digit', minute:'2-digit'}) : '--:--'"></span>
                </div>
            @endforeach
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
