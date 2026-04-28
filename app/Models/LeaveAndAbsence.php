<?php

/**
 * Ficheiro do Modelo LeaveAndAbsence.
 *
 * Este modelo gere os pedidos formais de licença e ausências justificadas
 * (ex: baixa médica, casamento, falecimento, etc.).
 * Inclui o workflow de aprovação e o rastreio de documentos de justificação.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LeaveAndAbsence extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Nome da tabela associada.
     *
     * @var string
     */
    protected $table = 'leaves_and_absences';

    /**
     * Campos preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',       // Funcionário que solicita a licença
        'type',              // Tipo de licença (ex: sick_leave, marriage)
        'start_date',        // Data de início
        'end_date',          // Data de fim
        'reason',            // Motivo detalhado
        'is_paid',           // Se a licença é remunerada ou não
        'justification_doc', // Caminho para o ficheiro de comprovativo/justificação
        'status',            // Estado do pedido (pending, approved, rejected)
        'approved_by',       // Utilizador que aprovou/rejeitou o pedido
        'rejection_reason'   // Motivo em caso de rejeição
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_paid' => 'boolean',
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
     *
     * Liga ao Utilizador (User) que tomou a decisão sobre este pedido.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Inicialização do modelo (Booting).
     *
     * Automatiza a atribuição do aprovador quando o estado do pedido muda.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            // Se o estado foi alterado para 'approved' ou 'rejected',
            // registamos automaticamente o ID do utilizador autenticado actual como o aprovador.
            if ($model->isDirty('status') && in_array($model->status, ['approved', 'rejected'])) {
                /** @noinspection PhpUndefinedMethodInspection - auth() helper returns Authenticatable **/
                $model->approved_by = auth()->id();
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
