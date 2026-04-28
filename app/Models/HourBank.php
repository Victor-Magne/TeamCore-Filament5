<?php

/**
 * Ficheiro do Modelo HourBank.
 *
 * Este modelo gere o Banco de Horas dos funcionários numa base mensal.
 * Armazena o saldo acumulado (positivo ou negativo), as horas extra ganhas
 * e as horas utilizadas (compensações), permitindo o transporte de saldos
 * entre meses.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class HourBank extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Nome da tabela.
     *
     * @var string
     */
    protected $table = 'hour_banks';

    /**
     * Campos preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',       // ID do funcionário
        'month_year',        // Mês e ano de referência (formato YYYY-MM)
        'balance',           // Saldo final do mês em minutos
        'extra_hours_added', // Minutos de horas extra acumulados no mês
        'extra_hours_used',  // Minutos de horas extra utilizados/compensados no mês
        'previous_balance',  // Saldo transportado do mês anterior
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'integer',
        'extra_hours_added' => 'integer',
        'extra_hours_used' => 'integer',
        'previous_balance' => 'integer',
    ];

    /**
     * Relacionamento: Funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Ausências.
     *
     * Liga às ausências (faltas/atrasos) registadas para este funcionário,
     * que afectam directamente o cálculo do saldo do banco de horas.
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class, 'employee_id', 'employee_id');
    }

    /**
     * Acessor para formatar o saldo de forma legível.
     *
     * Converte os minutos totais em formato "(-)Xh Ym".
     * Lida correctamente com saldos negativos adicionando o sinal de menos.
     *
     * @return string
     */
    public function getFormattedBalanceAttribute(): string
    {
        $hours = intdiv(abs($this->balance), 60);
        $minutes = abs($this->balance) % 60;
        $sign = $this->balance < 0 ? '-' : '';

        return "{$sign}{$hours}h {$minutes}m";
    }

    /**
     * Acessor para formatar as horas extra adicionadas.
     *
     * @return string
     */
    public function getFormattedExtraHoursAddedAttribute(): string
    {
        $hours = intdiv($this->extra_hours_added, 60);
        $minutes = $this->extra_hours_added % 60;

        return "{$hours}h {$minutes}m";
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
