<?php

/**
 * Ficheiro da Página AttendanceCheckIn.
 *
 * Esta página personalizada fornece uma interface simplificada para o funcionário
 * realizar a marcação de ponto diária. Implementa um sistema de botão dinâmico
 * que alterna entre Entrada, Início de Almoço, Fim de Almoço e Saída,
 * consoante o estado actual do dia do funcionário.
 */

namespace App\Filament\Pages;

use App\Models\AttendanceLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AttendanceCheckIn extends Page
{
    /**
     * Ícone de navegação.
     */
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    /**
     * Caminho do template Blade para renderização da página.
     */
    protected string $view = 'filament.pages.attendance-check-in';

    /**
     * Agrupamento no menu lateral.
     */
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Pessoal';

    /**
     * Propriedades para sincronização reactiva com o frontend.
     * Armazenam os timestamps das acções do dia actual.
     */
    public ?int $timeIn = null;
    public ?int $lunchStart = null;
    public ?int $lunchEnd = null;
    public ?int $timeOut = null;
    public int $serverTimestamp;

    /**
     * Método inicializador da página.
     *
     * Verifica se o utilizador autenticado tem um perfil de funcionário associado.
     * Caso contrário, redirecciona para fora da página por segurança.
     */
    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->employee_id) {
            $this->redirect(config('filament.path'));
            return;
        }
        $this->refreshTimestamps();
    }

    /**
     * Actualiza as propriedades da classe com os dados mais recentes da base de dados.
     * Filtra sempre pelo funcionário logado e pelo dia de hoje.
     */
    public function refreshTimestamps(): void
    {
        $log = AttendanceLog::where('employee_id', Auth::user()->employee_id)
            ->whereDate('time_in', Carbon::today())
            ->first();

        // Converte objectos Carbon para timestamps inteiros para facilitar o manuseamento em JS/Alpine
        $this->timeIn = $log?->time_in?->timestamp;
        $this->lunchStart = $log?->lunch_break_start?->timestamp;
        $this->lunchEnd = $log?->lunch_break_end?->timestamp;
        $this->timeOut = $log?->time_out?->timestamp;
        $this->serverTimestamp = now()->timestamp;
    }

    /**
     * Acção principal de marcação de ponto.
     *
     * Identifica qual a próxima fase do dia (Entrada -> Almoço -> Saída)
     * e actualiza o registo correspondente.
     */
    public function checkInAction(): Action
    {
        return Action::make('checkIn')
            ->label($this->getCheckInLabel()) // Rótulo dinâmico baseado no estado
            ->color($this->getCheckInColor()) // Cor dinâmica (ex: verde para entrada, vermelho para saída)
            ->icon($this->getCheckInIcon())
            ->size('xl')
            ->requiresConfirmation() // Pede confirmação para evitar cliques acidentais
            ->action(function () {
                $user = Auth::user();
                $now = Carbon::now();
                $log = AttendanceLog::where('employee_id', $user->employee_id)
                    ->whereDate('time_in', Carbon::today())
                    ->first();

                // Lógica de transição de estados
                if (! $log) {
                    // Primeiro registo do dia: Entrada
                    AttendanceLog::create(['employee_id' => $user->employee_id, 'time_in' => $now]);
                } elseif (! $log->lunch_break_start) {
                    // Segundo registo: Início de Almoço
                    $log->update(['lunch_break_start' => $now]);
                } elseif (! $log->lunch_break_end) {
                    // Terceiro registo: Regresso do Almoço
                    $log->update(['lunch_break_end' => $now]);
                } elseif (! $log->time_out) {
                    // Quarto registo: Saída
                    $log->update(['time_out' => $now]);
                }

                $this->refreshTimestamps(); // Sincroniza dados com a UI
                Notification::make()->title('Registo efectuado com sucesso')->success()->send();
            });
    }

    /**
     * Determina o texto do botão de acção com base nos registos existentes.
     */
    public function getCheckInLabel(): string
    {
        if (!$this->timeIn) return __('widgets.attendance.entry');
        if (!$this->lunchStart) return __('widgets.attendance.lunch_start');
        if (!$this->lunchEnd) return __('widgets.attendance.lunch_end');
        if (!$this->timeOut) return __('widgets.attendance.exit');
        return __('widgets.attendance.completed');
    }

    /**
     * Define a cor do botão consoante a acção (Verde, Amarelo, Azul, Vermelho).
     */
    protected function getCheckInColor(): string
    {
        $label = $this->getCheckInLabel();
        return match ($label) {
            __('widgets.attendance.entry') => 'success',
            __('widgets.attendance.lunch_start') => 'warning',
            __('widgets.attendance.lunch_end') => 'info',
            __('widgets.attendance.exit') => 'danger',
            default => 'gray',
        };
    }

    /**
     * Define o ícone do botão consoante a acção.
     */
    protected function getCheckInIcon(): string
    {
        $label = $this->getCheckInLabel();
        return match ($label) {
            __('widgets.attendance.entry') => 'heroicon-m-arrow-right-end-on-rectangle',
            __('widgets.attendance.lunch_start') => 'heroicon-m-cake',
            __('widgets.attendance.lunch_end') => 'heroicon-m-briefcase',
            __('widgets.attendance.exit') => 'heroicon-m-arrow-left-start-on-rectangle',
            default => 'heroicon-m-check-circle',
        };
    }
}
