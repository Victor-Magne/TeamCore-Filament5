<x-filament-panels::page>
    <style>
        .fi-attendance-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .fi-attendance-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .fi-attendance-card {
            border-radius: 0.75rem;
            background-color: #ffffff;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            outline: 1px solid rgb(3 7 18 / 0.05);
        }
        .dark .fi-attendance-card {
            background-color: #111827;
            outline-color: rgb(255 255 255 / 0.1);
        }
        .fi-attendance-card-inner {
            padding: 1.25rem 1.5rem;
        }
        .fi-attendance-stat-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
        }
        .fi-attendance-stat-value {
            margin-top: 0.75rem;
            font-family: ui-monospace, monospace;
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            font-variant-numeric: tabular-nums;
        }
        .dark .fi-attendance-stat-value {
            color: #f9fafb;
        }
        .fi-attendance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }
        .fi-attendance-clock {
            font-family: ui-monospace, monospace;
            font-size: 2.5rem;
            font-weight: 700;
            color: #111827;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.025em;
        }
        .dark .fi-attendance-clock {
            color: #f9fafb;
        }
        .fi-attendance-label-xs {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
        }
        .fi-attendance-name {
            margin-top: 0.25rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }
        .dark .fi-attendance-name {
            color: #f9fafb;
        }
        .fi-attendance-date {
            margin-top: 0.125rem;
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: capitalize;
        }
        .fi-attendance-action-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            padding: 2rem 1.5rem;
        }
        .fi-attendance-action-title {
            font-size: 0.9375rem;
            font-weight: 500;
            color: #374151;
        }
        .dark .fi-attendance-action-title {
            color: #d1d5db;
        }
        .fi-attendance-action-sub {
            margin-top: 0.25rem;
            font-size: 0.8125rem;
            color: #9ca3af;
        }
        .fi-attendance-action-btn {
            width: 100%;
            max-width: 18rem;
        }
        .fi-attendance-worked {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }
        .fi-attendance-worked-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .fi-attendance-worked-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            background-color: #fef7ed;
        }
        .dark .fi-attendance-worked-icon {
            background-color: rgb(230 127 26 / 0.15);
        }
        .fi-attendance-worked-value {
            font-family: ui-monospace, monospace;
            font-size: 1.875rem;
            font-weight: 600;
            color: #e67f1a;
            font-variant-numeric: tabular-nums;
        }
        .fi-attendance-icon-color-success { color: #10b981; }
        .fi-attendance-icon-color-warning { color: #f59e0b; }
        .fi-attendance-icon-color-info    { color: #3b82f6; }
        .fi-attendance-icon-color-danger  { color: #ef4444; }
    </style>

    <div
        class="space-y-4"
        x-data="{
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
        }"
    >
        {{-- Card: Saudação + Relógio --}}
        <div class="fi-attendance-card">
            <div class="fi-attendance-header">
                <div>
                    <p class="fi-attendance-label-xs">Controlo de ponto diário</p>
                    <p class="fi-attendance-name">Olá, {{ explode(' ', auth()->user()->name)[0] }}</p>
                    <p class="fi-attendance-date" x-text="date"></p>
                </div>
                <p class="fi-attendance-clock" x-text="time"></p>
            </div>
        </div>

        {{-- Card: Acção de ponto --}}
        <div class="fi-attendance-card">
            <div class="fi-attendance-action-wrap">
                <div class="text-center">
                    <p class="fi-attendance-action-title">{{ $this->getCheckInLabel() }}</p>
                    <p class="fi-attendance-action-sub">Clique para confirmar o registo de ponto</p>
                </div>
                <div class="fi-attendance-action-btn">
                    {{ $this->checkInAction }}
                </div>
            </div>
        </div>

        {{-- Cards: Timestamps do dia (grid 4x1) --}}
        <div class="fi-attendance-grid">
            @foreach([
                ['label' => 'Entrada',  'prop' => 'in',      'icon' => 'heroicon-m-arrow-right-end-on-rectangle', 'color' => 'fi-attendance-icon-color-success'],
                ['label' => 'Almoço',   'prop' => 'l_start', 'icon' => 'heroicon-m-pause-circle',                 'color' => 'fi-attendance-icon-color-warning'],
                ['label' => 'Regresso', 'prop' => 'l_end',   'icon' => 'heroicon-m-play-circle',                  'color' => 'fi-attendance-icon-color-info'],
                ['label' => 'Saída',    'prop' => 'out',     'icon' => 'heroicon-m-arrow-left-start-on-rectangle','color' => 'fi-attendance-icon-color-danger'],
            ] as $item)
                <div class="fi-attendance-card">
                    <div class="fi-attendance-card-inner">
                        <div class="fi-attendance-stat-label">
                            <x-filament::icon
                                :icon="$item['icon']"
                                class="h-4 w-4 {{ $item['color'] }}"
                            />
                            {{ $item['label'] }}
                        </div>
                        <p
                            class="fi-attendance-stat-value"
                            x-text="{{ $item['prop'] }} ? new Date({{ $item['prop'] }} * 1000).toLocaleTimeString('pt-PT', {hour:'2-digit',minute:'2-digit'}) : '——'"
                        ></p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Card: Tempo total trabalhado --}}
        <div class="fi-attendance-card">
            <div class="fi-attendance-worked">
                <div class="fi-attendance-worked-left">
                    <div class="fi-attendance-worked-icon">
                        <x-filament::icon icon="heroicon-m-clock" class="h-5 w-5" style="color:#e67f1a" />
                    </div>
                    <div>
                        <p class="fi-attendance-label-xs">Tempo total trabalhado</p>
                        <p class="fi-attendance-date" style="margin-top:0.125rem">Hoje</p>
                    </div>
                </div>
                <p class="fi-attendance-worked-value" x-text="workedTime"></p>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
