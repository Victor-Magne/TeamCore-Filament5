<?php

namespace App\Models;

use App\Casts\ValidateUtf8String;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'employee_id',
        'month_year',
        'base_salary',
        'hourly_rate',
        'extra_hours',
        'extra_hours_amount',
        'deductions',
        'total_net',
        'status',
    ];

    protected $casts = [
        'month_year' => ValidateUtf8String::class,
        'status' => ValidateUtf8String::class,
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'extra_hours' => 'integer',
        'extra_hours_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
