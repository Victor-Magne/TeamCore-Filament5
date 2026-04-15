<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HourBank extends Model
{
    protected $fillable = [
        'employee_id',
        'month_year',
        'balance',
        'extra_hours_added',
        'extra_hours_used',
        'previous_balance',
    ];

    protected $casts = [
        'balance' => 'integer',
        'extra_hours_added' => 'integer',
        'extra_hours_used' => 'integer',
        'previous_balance' => 'integer',
    ];

    /**
     * Relacionamento: Funcionário
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Ausências registadas neste período
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class, 'employee_id', 'employee_id');
    }

    /**
     * Formata o saldo em horas e minutos (ex: "8h 30m")
     */
    public function getFormattedBalanceAttribute(): string
    {
        $hours = intdiv(abs($this->balance), 60);
        $minutes = abs($this->balance) % 60;
        $sign = $this->balance < 0 ? '-' : '';

        return "{$sign}{$hours}h {$minutes}m";
    }

    /**
     * Formata horas extras adicionadas em horas e minutos
     */
    public function getFormattedExtraHoursAddedAttribute(): string
    {
        $hours = intdiv($this->extra_hours_added, 60);
        $minutes = $this->extra_hours_added % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Formata horas extras usadas em horas e minutos
     */
    public function getFormattedExtraHoursUsedAttribute(): string
    {
        $hours = intdiv($this->extra_hours_used, 60);
        $minutes = $this->extra_hours_used % 60;

        return "{$hours}h {$minutes}m";
    }
}
