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

    protected ?string $originalStatusForUpdate = null;

    protected ?int $originalDaysTakenForUpdate = null;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calculateDaysTaken(): void
    {
        if ($this->start_date && $this->end_date) {
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
        static::creating(function (self $model) {
            if (blank($model->year_reference)) {
                $model->year_reference = $model->start_date?->year ?? Carbon::now()->year;
            }

            $model->calculateDaysTaken();
        });

        static::updating(function (self $model) {
            $model->originalStatusForUpdate = $model->getOriginal('status');
            $model->originalDaysTakenForUpdate = (int) $model->getOriginal('days_taken');
            $model->calculateDaysTaken();
        });

        static::saving(function (self $model) {
            if ($model->isDirty('status') && in_array($model->status, ['approved', 'rejected'])) {
                $user = auth()->user();

                if ($user && $user->employee_id === $model->employee_id && ! $user->can('Approve:OwnVacation')) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'status' => 'Conflito de Interesses: Nao tem permissao para alterar o estado do seu proprio registo.',
                    ]);
                }

                $model->approved_by = $user?->id;
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

        static::updated(function (self $model) {
            $originalStatus = $model->originalStatusForUpdate ?? $model->getOriginal('status');
            $originalDaysTaken = $model->originalDaysTakenForUpdate ?? (int) $model->getOriginal('days_taken');
            $currentDaysTaken = (int) $model->days_taken;

            $employee = $model->employee;

            if (! $employee) {
                return;
            }

            if ($originalStatus !== $model->status) {
                if ($model->status === 'approved') {
                    $employee->decrement('vacation_balance', $currentDaysTaken);
                } elseif ($originalStatus === 'approved') {
                    $employee->increment('vacation_balance', $originalDaysTaken);
                }

                return;
            }

            if ($model->status === 'approved' && $originalDaysTaken !== $currentDaysTaken) {
                $diff = $currentDaysTaken - $originalDaysTaken;

                if ($diff > 0) {
                    $employee->decrement('vacation_balance', $diff);
                } elseif ($diff < 0) {
                    $employee->increment('vacation_balance', abs($diff));
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
