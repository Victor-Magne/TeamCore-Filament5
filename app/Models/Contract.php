<?php

namespace App\Models;

use App\Casts\ValidateUtf8String;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'designation_id',
        'type',
        'salary',
        'daily_work_minutes',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'type' => ValidateUtf8String::class,
        'status' => ValidateUtf8String::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
