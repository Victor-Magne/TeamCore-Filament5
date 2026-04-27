<?php

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

    protected $fillable = ['employee_id', 'time_in', 'lunch_break_start', 'lunch_break_end', 'time_out', 'total_minutes', 'metadata', 'notes'];

    protected $casts = [
        'time_in' => 'datetime',
        'lunch_break_start' => 'datetime',
        'lunch_break_end' => 'datetime',
        'time_out' => 'datetime',
        'metadata' => 'json',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calcula e atualiza o tempo total em minutos (entrada - saída, excluindo almoço)
     * Só calcula se time_in e time_out estão preenchidos
     */
    public function calculateTotalMinutes(): ?int
    {
        if (! $this->time_in || ! $this->time_out) {
            return null;
        }

        $totalMinutes = $this->time_in->diffInMinutes($this->time_out);

        // Obter contrato para saber duração do almoço esperada
        $contract = $this->employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $this->time_in)
            ->orderByDesc('start_date')
            ->first();

        $expectedLunchMinutes = $contract?->lunch_duration_minutes ?? 60;

        // Subtrair o tempo de almoço efetivo ou o esperado (o que for maior)
        if ($this->lunch_break_start && $this->lunch_break_end) {
            $actualLunchMinutes = $this->lunch_break_start->diffInMinutes($this->lunch_break_end);
            $lunchToDeduct = max($actualLunchMinutes, $expectedLunchMinutes);
            $totalMinutes -= $lunchToDeduct;
        } else {
            // Se não registou almoço, desconta o esperado por defeito
            $totalMinutes -= $expectedLunchMinutes;
        }

        return max(0, $totalMinutes); // Garantir que não é negativo
    }

    /**
     * Hook automático: calcula total_minutes antes de guardar
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->total_minutes = $model->calculateTotalMinutes();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
