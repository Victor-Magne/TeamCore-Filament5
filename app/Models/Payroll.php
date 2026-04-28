<?php

/**
 * Ficheiro do Modelo Payroll.
 *
 * Este modelo representa o processamento salarial mensal de um funcionário.
 * Consolida o salário base, ganhos por horas extra e eventuais deduções
 * para calcular o valor líquido final a ser pago.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',        // Funcionário a quem este recibo pertence
        'month_year',         // Mês e ano do processamento (YYYY-MM)
        'base_salary',        // Salário base definido no contrato
        'hourly_rate',        // Valor calculado da hora de trabalho
        'extra_hours',        // Minutos totais de horas extra processados
        'extra_hours_amount', // Valor monetário a pagar pelas horas extra
        'deductions',         // Valor total de descontos (ex: faltas, impostos)
        'total_net',          // Valor líquido final a pagar
        'status',             // Estado do processamento (draft, processed, paid)
    ];

    /**
     * Conversões de tipos para garantir precisão monetária.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'extra_hours' => 'integer',
        'extra_hours_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    /**
     * Relacionamento: Funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
