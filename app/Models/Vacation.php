<?php

namespace App\Models;

use App\Services\Vacation\VacationBalanceService;
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

    protected $fillable = [
        'employee_id',
        'year_reference',
        'start_date',
        'end_date',
        'days_taken',
        'status',
        'approved_by',
        'rejection_reason',
    ];

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

    public function calculateDaysTaken(): void
    {
        if ($this->start_date && $this->end_date) {
            $daysDiff = $this->start_date->diffInDays($this->end_date) + 1;
            $this->days_taken = max(1, $daysDiff);
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
            $model->calculateDaysTaken();
        });

        // O aprovador é passado pelo chamador (form/action) — não inferir de auth() aqui.
        // O campo approved_by deve ser preenchido explicitamente ao mudar o status.

        static::created(function (self $model) {
            if ($model->status === 'approved') {
                app(VacationBalanceService::class)->deductOnApproval($model);
            }
        });

        static::updated(function (self $model) {
            $service = app(VacationBalanceService::class);

            if ($model->wasChanged('status')) {
                if ($model->status === 'approved') {
                    $service->deductOnApproval($model);
                } elseif ($model->getOriginal('status') === 'approved') {
                    $service->restoreOnRevocation($model);
                }
            } elseif ($model->wasChanged('days_taken') && $model->status === 'approved') {
                $service->adjustOnDaysChange($model);
            }
        });

        static::deleted(function (self $model) {
            app(VacationBalanceService::class)->restoreOnDelete($model);
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
