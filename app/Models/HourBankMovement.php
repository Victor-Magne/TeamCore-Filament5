<?php

/**
 * Ficheiro do Modelo HourBankMovement.
 *
 * Este modelo regista cada movimento (adição ou dedução) no banco de horas.
 * Serve como histórico detalhado e auditoria para o saldo acumulado.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class HourBankMovement extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'amount',
        'type',
        'source_type',
        'source_id',
        'description',
        'date',
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'date' => 'date',
    ];

    /**
     * Relacionamento: Funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento Polimórfico: Origem do movimento.
     * Pode ser um AttendanceLog ou uma Absence.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Acessor para formatar o valor de forma legível.
     */
    public function getFormattedAmountAttribute(): string
    {
        $hours = intdiv(abs($this->amount), 60);
        $minutes = abs($this->amount) % 60;
        $sign = $this->amount < 0 ? '-' : '+';

        return "{$sign}{$hours}h {$minutes}m";
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
