<?php

/**
 * Ficheiro do Modelo Vacation.
 *
 * Este modelo gere o ciclo de vida dos pedidos de férias dos funcionários.
 * Controla as datas de gozo, o estado de aprovação e automatiza a dedução/reposição
 * do saldo de dias de férias do funcionário consoante as alterações no pedido.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Vacation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',     // Funcionário que solicita as férias
        'year_reference',  // Ano a que as férias dizem respeito (ex: 2024)
        'start_date',      // Primeiro dia de férias
        'end_date',        // Último dia de férias
        'days_taken',      // Quantidade total de dias gozados (calculado)
        'status',          // Estado (pending, approved, rejected)
        'approved_by',     // Utilizador que tomou a decisão
        'rejection_reason' // Motivo caso o pedido seja recusado
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relacionamento: Funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento: Aprovador.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calcula automaticamente a diferença de dias entre a data de início e fim.
     * Inclui o dia de início e fim no cálculo (+1).
     *
     * @return void
     */
    public function calculateDaysTaken(): void
    {
        if ($this->start_date && $this->end_date) {
            $daysDiff = $this->start_date->diffInDays($this->end_date) + 1;
            $this->days_taken = max(1, $daysDiff);
        }
    }

    /**
     * Inicialização do modelo (Booting) e definição de Eventos (Hooks).
     *
     * Contém a lógica de negócio central para gestão automática de saldos de férias.
     */
    protected static function booted(): void
    {
        // Ao criar um novo pedido, define o ano de referência se estiver vazio.
        static::creating(function (self $model) {
            if (blank($model->year_reference)) {
                $model->year_reference = $model->start_date?->year ?? Carbon::now()->year;
            }
        });

        // Garante o cálculo dos dias gozados antes de persistir na base de dados.
        static::creating(function (self $model) {
            $model->calculateDaysTaken();
        });

        static::updating(function (self $model) {
            $model->calculateDaysTaken();
        });

        // Atribui automaticamente o aprovador quando o estado muda.
        static::saving(function (self $model) {
            if ($model->isDirty('status') && in_array($model->status, ['approved', 'rejected'])) {
                $model->approved_by = auth()->id();
            }
        });

        // Caso o pedido seja criado já com estado 'approved', deduz logo do saldo do funcionário.
        static::created(function (self $model) {
            if ($model->status === 'approved') {
                $employee = $model->employee;
                if ($employee && $model->days_taken > 0) {
                    $employee->decrement('vacation_balance', $model->days_taken);
                }
            }
        });

        // Gere a dedução ou reposição do saldo quando o estado ou duração do pedido muda.
        static::updated(function (self $model) {
            if ($model->wasChanged('status')) {
                $employee = $model->employee;
                if (! $employee) {
                    return;
                }

                if ($model->status === 'approved') {
                    // Se foi aprovado agora, subtrai os dias.
                    $employee->decrement('vacation_balance', $model->days_taken);
                } elseif ($model->getOriginal('status') === 'approved') {
                    // Se dantes estava aprovado e agora já não está (ex: cancelado), devolve os dias.
                    $employee->increment('vacation_balance', $model->getOriginal('days_taken'));
                }
            } elseif ($model->wasChanged('days_taken') && $model->status === 'approved') {
                // Se a duração mudou mas continua aprovado, ajusta a diferença no saldo.
                $employee = $model->employee;
                if ($employee) {
                    $diff = $model->days_taken - $model->getOriginal('days_taken');
                    if ($diff > 0) {
                        $employee->decrement('vacation_balance', $diff);
                    } elseif ($diff < 0) {
                        $employee->increment('vacation_balance', abs($diff));
                    }
                }
            }
        });

        // Se um pedido aprovado for eliminado, devolve os dias ao funcionário.
        static::deleted(function (self $model) {
            if ($model->status === 'approved') {
                $employee = $model->employee;
                if ($employee) {
                    $employee->increment('vacation_balance', $model->days_taken);
                }
            }
        });
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
