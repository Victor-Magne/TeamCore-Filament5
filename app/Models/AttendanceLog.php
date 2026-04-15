<?php

namespace App\Models;

use App\Services\Hour\CalculateExtraHoursService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
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

        // Subtrair o tempo de almoço se ambas as datas existem
        if ($this->lunch_break_start && $this->lunch_break_end) {
            $lunchMinutes = $this->lunch_break_start->diffInMinutes($this->lunch_break_end);
            $totalMinutes -= $lunchMinutes;
        }

        return max(0, $totalMinutes); // Garantir que não é negativo
    }

    /**
     * Hook automático: calcula total_minutes antes de guardar e horas extras após guardar
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->total_minutes = $model->calculateTotalMinutes();
        });

        static::saved(function (self $model) {
            // Calcular e registar horas extras no banco de horas
            $service = new CalculateExtraHoursService;
            $service->handle($model);
        });
    }
}
