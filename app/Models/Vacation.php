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
     */
    public function calculateDaysTaken(): void
    {
        if ($this->start_date && $this->end_date) {
            $daysDiff = $this->start_date->diffInDays($this->end_date) + 1;
            $this->days_taken = max(1, $daysDiff);
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

        // Deduzir do saldo de férias ao aprovar
        static::saved(function (self $model) {
            if ($model->wasChanged('status') && $model->status === 'approved') {
                $employee = $model->employee;
                if ($employee && $model->days_taken > 0) {
                    $employee->decrement('vacation_balance', $model->days_taken);
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
