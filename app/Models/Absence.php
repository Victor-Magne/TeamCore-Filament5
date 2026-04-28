<?php

/**
 * Ficheiro do Modelo Absence.
 *
 * Este modelo representa os registos de ausências e deduções de horas dos funcionários.
 * É utilizado principalmente para auditar e rastrear o porquê de certas horas terem sido
 * descontadas do banco de horas (por exemplo, faltas injustificadas ou atrasos).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Absence extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',            // ID do funcionário associado
        'leave_and_absence_id',   // ID do pedido de licença associado (se aplicável)
        'absence_date',           // Data em que ocorreu a ausência
        'hours_deducted',         // Quantidade de minutos descontados
        'deduction_type',         // Tipo de dedução (ex: falta injustificada, atraso)
        'reason',                 // Descrição ou motivo detalhado da ausência
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'absence_date' => 'date',     // Garante que a data seja tratada como um objecto Carbon
        'hours_deducted' => 'integer', // Garante que os minutos sejam tratados como inteiro para cálculos
    ];

    /**
     * Relacionamento: Funcionário.
     *
     * Liga a ausência ao funcionário correspondente. É essencial para saber
     * a quem pertence o desconto de horas.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Pedido de Licença/Ausência.
     *
     * Liga este registo de ausência a um pedido formal de licença (LeaveAndAbsence),
     * permitindo rastrear se a dedução teve origem num pedido submetido.
     */
    public function leaveAndAbsence(): BelongsTo
    {
        return $this->belongsTo(LeaveAndAbsence::class);
    }

    /**
     * Acessor para obter as horas descontadas formatadas de forma legível.
     *
     * Converte o valor armazenado em minutos (ex: 90) para uma string
     * no formato "Xh Ym" (ex: 1h 30m) para exibição na interface.
     */
    public function getFormattedHoursDeductedAttribute(): string
    {
        // Calcula o número inteiro de horas dividindo os minutos por 60
        $hours = intdiv($this->hours_deducted, 60);
        // Obtém o resto da divisão para encontrar os minutos restantes
        $minutes = $this->hours_deducted % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Configuração das opções de registo de actividade (Log).
     *
     * Define como o Spatie Activity Log deve registar as alterações neste modelo.
     * Regista todos os campos e apenas quando há mudanças efectivas.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()           // Regista todos os atributos do modelo
            ->logOnlyDirty()     // Regista apenas os atributos que foram alterados
            ->useLogName(class_basename($this)); // Usa o nome da classe como identificador do log
    }
}
