<?php

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

    protected $fillable = ['employee_id', 'year_reference', 'start_date', 'end_date', 'days_taken', 'status', 'approved_by', 'rejection_reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calcular automaticamente os dias gozados entre as datas
     * ⚠️ IMPORTANTE: Conta apenas dias ÚTEIS (segunda a sexta)
     * Se precisar contar feriados nacionais, adicione uma verificação aqui
     */
    public function calculateDaysTaken(): void
    {
        if ($this->start_date && $this->end_date) {
            // Contar apenas dias úteis (segunda a sexta)
            $workingDays = 0;
            $currentDate = $this->start_date->copy();

            while ($currentDate->lte($this->end_date)) {
                if ($currentDate->isWeekday()) {
                    $workingDays++;
                }
                $currentDate->addDay();
            }

            $this->days_taken = max(1, $workingDays);
        }
    }

    protected static function booted(): void
    {
        // Calcular year_reference automaticamente se não estiver definido
        static::creating(function (self $model) {
            if (blank($model->year_reference)) {
                $model->year_reference = $model->start_date?->year ?? Carbon::now()->year;
            }
        });

        // Calcular days_taken automaticamente ao criar ou atualizar
        static::creating(function (self $model) {
            $model->calculateDaysTaken();
        });

        static::updating(function (self $model) {
            $model->calculateDaysTaken();
        });

        // Atualizar approved_by ao salvar
        static::saving(function (self $model) {
            if ($model->isDirty('status') && in_array($model->status, ['approved', 'rejected'])) {
                $model->approved_by = auth()->id();
            }
        });

        static::created(function (self $model) {
            if ($model->status === 'approved') {
                $employee = $model->employee;
                if ($employee && $model->days_taken > 0) {
                    $employee->decrement('vacation_balance', $model->days_taken);
                }
            }
        });

        // Deduzir do saldo de férias ao aprovar ou restaurar se desaprovado
        static::updated(function (self $model) {
            if ($model->wasChanged('status')) {
                $employee = $model->employee;
                if (! $employee) {
                    return;
                }

                if ($model->status === 'approved') {
                    $employee->decrement('vacation_balance', $model->days_taken);
                } elseif ($model->getOriginal('status') === 'approved') {
                    $employee->increment('vacation_balance', $model->getOriginal('days_taken'));
                }
            } elseif ($model->wasChanged('days_taken') && $model->status === 'approved') {
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

        static::deleted(function (self $model) {
            if ($model->status === 'approved') {
                $employee = $model->employee;
                if ($employee) {
                    $employee->increment('vacation_balance', $model->days_taken);
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
