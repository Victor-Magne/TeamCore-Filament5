<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_and_absence_id',
        'absence_date',
        'hours_deducted',
        'deduction_type',
        'reason',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'hours_deducted' => 'integer',
    ];

    /**
     * Relacionamento: Funcionário
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Leave/Absence da tabela existing
     */
    public function leaveAndAbsence(): BelongsTo
    {
        return $this->belongsTo(LeaveAndAbsence::class);
    }

    /**
     * Formata as horas descontadas em horas e minutos
     */
    public function getFormattedHoursDeductedAttribute(): string
    {
        $hours = intdiv($this->hours_deducted, 60);
        $minutes = $this->hours_deducted % 60;

        return "{$hours}h {$minutes}m";
    }
}
