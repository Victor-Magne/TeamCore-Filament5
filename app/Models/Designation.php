<?php

namespace App\Models;

use App\Casts\ValidateUtf8String;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Designation extends Model
{
    protected $fillable = ['name', 'level', 'base_salary'];

    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $casts = [
        'name' => ValidateUtf8String::class,
        'base_salary' => 'decimal:2',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
