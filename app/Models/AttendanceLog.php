<?php

/**
 * Ficheiro do Modelo AttendanceLog.
 *
 * Este modelo gere os registos diários de presença dos funcionários.
 * Controla os horários de entrada, saída e pausas para almoço, sendo a base
 * fundamental para o cálculo das horas trabalhadas e posterior gestão do banco de horas.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class AttendanceLog extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',         // ID do funcionário
        'time_in',            // Hora de entrada
        'lunch_break_start',  // Início da pausa de almoço
        'lunch_break_end',    // Fim da pausa de almoço
        'time_out',           // Hora de saída
        'total_minutes',      // Total de minutos trabalhados (calculado)
        'metadata',           // Dados adicionais em formato JSON (ex: IP, Browser)
        'notes'               // Notas ou observações sobre o registo
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time_in' => 'datetime',
        'lunch_break_start' => 'datetime',
        'lunch_break_end' => 'datetime',
        'time_out' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Relacionamento: Funcionário.
     *
     * Liga o registo de presença ao funcionário que realizou a marcação.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calcula o tempo total trabalhado em minutos, subtraindo o tempo de almoço.
     *
     * Esta é uma função crítica que assegura que o tempo de almoço é respeitado.
     * Se o tempo de almoço registado for inferior ao definido no contrato,
     * utiliza-se o valor contratual para a dedução (protecção de tempos mínimos de descanso).
     *
     * @return int|null Retorna o total de minutos ou null se a saída ainda não foi registada.
     */
    public function calculateTotalMinutes(): ?int
    {
        // Só é possível calcular o total se tivermos a entrada e a saída
        if (! $this->time_in || ! $this->time_out) {
            return null;
        }

        // Diferença bruta entre entrada e saída
        $totalMinutes = $this->time_in->diffInMinutes($this->time_out);

        // Procurar o contrato activo para saber a duração de almoço esperada
        $contract = $this->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $this->time_in)
            ->orderByDesc('start_date')
            ->first();

        // Se não houver contrato, assume-se 60 minutos por defeito
        $expectedLunchMinutes = $contract?->lunch_duration_minutes ?? 60;

        // Lógica de dedução de almoço
        if ($this->lunch_break_start && $this->lunch_break_end) {
            // Tempo efectivamente passado em pausa
            $actualLunchMinutes = $this->lunch_break_start->diffInMinutes($this->lunch_break_end);

            // Desconta o maior valor entre o real e o esperado (garante cumprimento do contrato)
            $lunchToDeduct = max($actualLunchMinutes, $expectedLunchMinutes);
            $totalMinutes -= $lunchToDeduct;
        } else {
            // Se não registou pausa de almoço, deduz automaticamente o tempo contratual
            $totalMinutes -= $expectedLunchMinutes;
        }

        // Garante que o resultado não é negativo em caso de registos inconsistentes
        return max(0, $totalMinutes);
    }

    /**
     * Inicialização do modelo (Booting).
     *
     * Define comportamentos automáticos despoletados por eventos do Eloquent.
     */
    protected static function booted(): void
    {
        // Sempre que o modelo for guardado (saving), recalculamos os minutos totais.
        // Isto garante a integridade dos dados mesmo que o valor não seja enviado manualmente.
        static::saving(function (self $model) {
            $model->total_minutes = $model->calculateTotalMinutes();
        });

    }

    /**
     * Configuração do log de actividades.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
