<?php

/**
 * Ficheiro do Modelo HourBank.
 *
 * Este modelo gere o Banco de Horas dos funcionários de forma acumulada (total).
 * Armazena o saldo total, o total de horas ganhas e o total de horas utilizadas
 * ao longo de toda a relação contratual do funcionário.
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
        'balance',           // Saldo actual acumulado em minutos
        'extra_hours_added', // Total acumulado de minutos ganhos
        'extra_hours_used',  // Total acumulado de minutos utilizados/compensados
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
    ];

    /**
     * Relacionamento: Funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Movimentos.
     *
     * Histórico detalhado de todas as alterações ao saldo.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(HourBankMovement::class, 'employee_id', 'employee_id');
    }

    /**
     * Acessor para formatar o saldo de forma legível.
     *
     * Converte os minutos totais em formato "(-)Xh Ym".
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
     * Acessor para formatar as horas extra utilizadas.
     *
     * @return string
     */
    public function getFormattedExtraHoursUsedAttribute(): string
    {
        $hours = intdiv($this->extra_hours_used, 60);
        $minutes = $this->extra_hours_used % 60;

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
