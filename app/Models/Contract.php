<?php

/**
 * Ficheiro do Modelo Contract.
 *
 * Este modelo gere as informações contratuais dos funcionários.
 * Define detalhes críticos como o salário, horário de trabalho esperado,
 * duração do almoço e o tipo de vínculo laboral (ex: sem termo, termo certo).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Contract extends Model
{
    // LogsActivity adicionado para garantir que alterações no contrato são auditadas
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',            // Funcionário a quem o contrato pertence
        'designation_id',         // Cargo/Função associada a este contrato
        'type',                   // Tipo de contrato (ex: permanent, fixed_term)
        'salary',                 // Valor da remuneração base
        'daily_work_minutes',     // Carga horária diária esperada em minutos (ex: 480 para 8h)
        'expected_start_time',    // Hora prevista de entrada (ex: "09:00")
        'lunch_duration_minutes', // Duração prevista para o almoço (ex: 60)
        'status',                 // Estado do contrato (active, terminated, on_hold)
        'start_date',             // Data de início do contrato
        'end_date',               // Data de fim (se aplicável)
    ];

    /**
     * Conversões de tipos de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2', // Mantém a precisão monetária de 2 casas decimais
    ];

    /**
     * Relacionamento: Funcionário.
     *
     * Liga o contrato ao funcionário correspondente.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Cargo (Designation).
     *
     * Obtém a definição da função/cargo que este contrato formaliza.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Configuração do log de actividades.
     *
     * Essencial para rastrear alterações salariais ou de carga horária por motivos de auditoria.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
