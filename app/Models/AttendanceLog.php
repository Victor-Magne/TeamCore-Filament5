<?php

namespace App\Models;

use App\Services\Attendance\AttendanceCalculationService;
use App\Services\Hour\HourBankService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class AttendanceLog extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'time_in',
        'lunch_break_start',
        'lunch_break_end',
        'time_out',
        'total_minutes',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'lunch_break_start' => 'datetime',
        'lunch_break_end' => 'datetime',
        'time_out' => 'datetime',
        'metadata' => 'json',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Mantido para compatibilidade — delega ao serviço.
     */
    public function calculateTotalMinutes(): ?int
    {
        return app(AttendanceCalculationService::class)->calculateTotalMinutes($this);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->total_minutes = $model->calculateTotalMinutes();
        });

        static::deleted(function (self $model) {
            app(HourBankService::class)->removeMovement(self::class, $model->id);
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
