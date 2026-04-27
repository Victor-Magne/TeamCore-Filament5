<?php

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

    protected $table = 'leaves_and_absences';

    protected $fillable = ['employee_id', 'type', 'start_date', 'end_date', 'reason', 'is_paid', 'justification_doc', 'status', 'approved_by', 'rejection_reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->isDirty('status') && in_array($model->status, ['approved', 'rejected'])) {
                /** @var \App\Models\User|null $user */
                $user = auth()->user();
                if ($user && $user->employee_id === $model->employee_id && ! $user->can('Approve:OwnLeaveAndAbsence')) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'status' => 'Conflito de Interesses: Não tem permissão para alterar o estado do seu próprio registo.',
                    ]);
                }
                $model->approved_by = $user?->id;
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
