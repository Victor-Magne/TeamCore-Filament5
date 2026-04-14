<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = ['employee_id', 'recorded_at', 'type', 'metadata', 'notes'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}