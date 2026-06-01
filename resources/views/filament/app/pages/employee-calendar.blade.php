<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/locales/pt.global.min.js"></script>

    <style>
        /* ── Calendário ao estilo Filament ── */
        .fi-calendar-wrapper .fc { font-family: inherit; }

        .fi-calendar-wrapper .fc .fc-toolbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }
        .fi-calendar-wrapper .fc .fc-button {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.04);
            transition: background-color 100ms, border-color 100ms, color 100ms;
            line-height: 1.5;
        }
        .fi-calendar-wrapper .fc .fc-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgb(230 127 26 / 0.2);
        }
        .fi-calendar-wrapper .fc .fc-button:hover:not(:disabled) {
            background-color: #f9fafb;
            border-color: #d1d5db;
        }
        .fi-calendar-wrapper .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fi-calendar-wrapper .fc .fc-button-primary:not(:disabled):active {
            background-color: #e67f1a;
            border-color: #d06513;
            color: #ffffff;
            box-shadow: none;
        }
        .fi-calendar-wrapper .fc .fc-button-group .fc-button { border-radius: 0; }
        .fi-calendar-wrapper .fc .fc-button-group .fc-button:first-child { border-radius: 0.5rem 0 0 0.5rem; }
        .fi-calendar-wrapper .fc .fc-button-group .fc-button:last-child  { border-radius: 0 0.5rem 0.5rem 0; }

        .fi-calendar-wrapper .fc .fc-col-header-cell {
            background-color: #f9fafb;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .fi-calendar-wrapper .fc .fc-col-header-cell-cushion {
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-decoration: none;
        }
        .fi-calendar-wrapper .fc .fc-daygrid-day-number {
            color: #374151;
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 0.375rem 0.5rem;
            text-decoration: none;
        }
        .fi-calendar-wrapper .fc .fc-daygrid-day-number:hover { color: #e67f1a; }
        .fi-calendar-wrapper .fc .fc-day-other .fc-daygrid-day-number { color: #d1d5db; }
        .fi-calendar-wrapper .fc .fc-daygrid-day.fc-day-today { background-color: #fef7ed; }
        .fi-calendar-wrapper .fc .fc-day-today .fc-daygrid-day-number { color: #e67f1a; font-weight: 700; }

        .fi-calendar-wrapper .fc td,
        .fi-calendar-wrapper .fc th { border-color: #f3f4f6; }
        .fi-calendar-wrapper .fc .fc-scrollgrid {
            border-color: #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .fi-calendar-wrapper .fc .fc-event {
            border: none;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 1px 5px;
            cursor: pointer;
        }
        .fi-calendar-wrapper .fc .fc-event:hover { filter: brightness(0.92); }
        .fi-calendar-wrapper .fc .fc-list-event:hover td { background-color: #fef7ed; }
        .fi-calendar-wrapper .fc .fc-list-day-cushion { background-color: #f9fafb; }

        /* Dark mode */
        .dark .fi-calendar-wrapper .fc .fc-toolbar-title { color: #f9fafb; }
        .dark .fi-calendar-wrapper .fc .fc-button { background-color: #1f2937; border-color: #374151; color: #d1d5db; }
        .dark .fi-calendar-wrapper .fc .fc-button:hover:not(:disabled) { background-color: #374151; }
        .dark .fi-calendar-wrapper .fc .fc-col-header-cell { background-color: #111827; }
        .dark .fi-calendar-wrapper .fc .fc-col-header-cell-cushion { color: #9ca3af; }
        .dark .fi-calendar-wrapper .fc .fc-daygrid-day-number { color: #d1d5db; }
        .dark .fi-calendar-wrapper .fc .fc-day-other .fc-daygrid-day-number { color: #4b5563; }
        .dark .fi-calendar-wrapper .fc .fc-daygrid-day.fc-day-today { background-color: rgb(230 127 26 / 0.1); }
        .dark .fi-calendar-wrapper .fc td,
        .dark .fi-calendar-wrapper .fc th { border-color: #1f2937; }
        .dark .fi-calendar-wrapper .fc .fc-scrollgrid { border-color: #374151; }
        .dark .fi-calendar-wrapper .fc .fc-list-day-cushion { background-color: #111827; }

        /* ── Legenda ── */
        .fi-cal-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            border-radius: 0.75rem;
            background: #ffffff;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            outline: 1px solid rgb(3 7 18 / 0.05);
            overflow: hidden;
        }
        .dark .fi-cal-legend {
            background: #111827;
            outline-color: rgb(255 255 255 / 0.1);
        }
        .fi-cal-legend-group {
            flex: 1 1 auto;
            min-width: 10rem;
            padding: 0.875rem 1.25rem;
            border-right: 1px solid #f3f4f6;
        }
        .fi-cal-legend-group:last-child { border-right: none; }
        .dark .fi-cal-legend-group { border-right-color: #1f2937; }
        .fi-cal-legend-group-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 0.625rem;
        }
        .fi-cal-legend-items {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        .fi-cal-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .fi-cal-legend-dot {
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }
        .fi-cal-legend-label {
            font-size: 0.8125rem;
            color: #374151;
        }
        .dark .fi-cal-legend-label { color: #d1d5db; }
    </style>

    <div
        wire:ignore
        x-data="{
            calendar: null,
            events: @js($this->calendarEvents),
            init() {
                this.$nextTick(() => {
                    this.calendar = new FullCalendar.Calendar(this.$refs.calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'pt',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listMonth',
                        },
                        events: this.events,
                        eventClick: (info) => {
                            const props = info.event.extendedProps;
                            let msg = '';
                            if (props.type === 'vacation') {
                                const statusLabel = props.status === 'approved' ? 'Aprovado' : props.status === 'pending' ? 'Pendente' : props.status;
                                msg = 'Férias\nEstado: ' + statusLabel + (props.days ? '\nDias: ' + props.days : '');
                            } else if (props.type === 'leave') {
                                const statusLabel = props.status === 'approved' ? 'Aprovado' : props.status === 'pending' ? 'Pendente' : props.status;
                                msg = info.event.title + '\nEstado: ' + statusLabel;
                                if (props.reason) msg += '\nMotivo: ' + props.reason;
                            } else if (props.type === 'absence') {
                                msg = 'Falta';
                                if (props.reason) msg += '\nMotivo: ' + props.reason;
                            }
                            alert(msg);
                        },
                        height: 'auto',
                        buttonText: {
                            today: 'Hoje',
                            month: 'Mês',
                            week: 'Semana',
                            list: 'Lista',
                        },
                    });
                    this.calendar.render();
                });

                const refreshHandler = (e) => {
                    if (! this.calendar) { return; }
                    this.calendar.removeAllEvents();
                    (e.detail.events || []).forEach(ev => this.calendar.addEvent(ev));
                };
                window.addEventListener('calendar-refresh', refreshHandler);
                this.$cleanup(() => window.removeEventListener('calendar-refresh', refreshHandler));
            },
        }"
        class="fi-calendar-wrapper"
    >
        <div
            x-ref="calendarEl"
            class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        ></div>
    </div>

    {{-- Legenda em grupos --}}
    <div class="fi-cal-legend">
        <div class="fi-cal-legend-group">
            <p class="fi-cal-legend-group-title">Férias</p>
            <div class="fi-cal-legend-items">
                <div class="fi-cal-legend-item">
                    <span class="fi-cal-legend-dot" style="background:#10b981"></span>
                    <span class="fi-cal-legend-label">Aprovadas</span>
                </div>
                <div class="fi-cal-legend-item">
                    <span class="fi-cal-legend-dot" style="background:#f59e0b"></span>
                    <span class="fi-cal-legend-label">Pendentes</span>
                </div>
            </div>
        </div>

        <div class="fi-cal-legend-group">
            <p class="fi-cal-legend-group-title">Licenças</p>
            <div class="fi-cal-legend-items">
                <div class="fi-cal-legend-item">
                    <span class="fi-cal-legend-dot" style="background:#3b82f6"></span>
                    <span class="fi-cal-legend-label">Aprovadas</span>
                </div>
                <div class="fi-cal-legend-item">
                    <span class="fi-cal-legend-dot" style="background:#93c5fd"></span>
                    <span class="fi-cal-legend-label">Pendentes</span>
                </div>
            </div>
        </div>

        <div class="fi-cal-legend-group">
            <p class="fi-cal-legend-group-title">Faltas</p>
            <div class="fi-cal-legend-items">
                <div class="fi-cal-legend-item">
                    <span class="fi-cal-legend-dot" style="background:#ef4444"></span>
                    <span class="fi-cal-legend-label">Falta registada</span>
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
